# Build arguments (can be overridden in docker-compose.yml)
ARG PHP_VERSION=8.3-fpm-alpine
ARG COMPOSER_VERSION=2
ARG WORKDIR=/var/www/html
ARG API_PORT=80

# Composer stage - extract composer binary
FROM composer:${COMPOSER_VERSION} AS composer

# Base stage with PHP 8.3 and extensions
FROM php:${PHP_VERSION} AS base

# Redeclare ARGs for use in this stage (not used in FROM)
ARG WORKDIR=/var/www/html

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    libxml2-dev \
    mysql-client \
    postgresql-dev \
    && rm -rf /var/cache/apk/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    pdo_pgsql \
    mysqli \
    gd \
    zip \
    opcache \
    intl \
    xml \
    mbstring \
    pcntl \
    bcmath \
    exif

# Install Redis extension via PECL
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Install Composer (from composer stage)
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR ${WORKDIR}

# Copy composer files
COPY composer.json composer.lock ./

# Configure Nginx
RUN rm -rf /etc/nginx/http.d/default.conf
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Configure PHP
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Configure Supervisor
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create necessary directories and set permissions
RUN mkdir -p ${WORKDIR}/storage/logs \
    ${WORKDIR}/storage/framework/sessions \
    ${WORKDIR}/storage/framework/views \
    ${WORKDIR}/storage/framework/cache \
    ${WORKDIR}/bootstrap/cache \
    /var/log/supervisor \
    && chown -R www-data:www-data ${WORKDIR} \
    && chmod -R 775 ${WORKDIR}/storage \
    && chmod -R 775 ${WORKDIR}/bootstrap/cache

# Development stage
FROM base AS development

# Redeclare ARGs for this stage
ARG WORKDIR=/var/www/html
ARG API_PORT=80

# Install development dependencies
RUN apk add --no-cache nodejs npm

# Install composer dependencies (including dev)
RUN composer install --no-interaction --no-progress --prefer-dist

# Copy application code
COPY --chown=www-data:www-data . .

# Expose port
EXPOSE ${API_PORT}

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

# Production stage
FROM base AS production

# Redeclare ARGs for this stage
ARG WORKDIR=/var/www/html
ARG API_PORT=80

# Ensure we're in the correct working directory
WORKDIR ${WORKDIR}

# Set production PHP configuration
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_REVALIDATE_FREQ=0
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0

# Install composer dependencies (production only)
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --no-progress --prefer-dist --optimize-autoloader

# Copy application code
COPY --chown=www-data:www-data . .

# Regenerate optimized autoload files (now that code is present)
RUN composer dump-autoload --optimize --classmap-authoritative --no-scripts

# Create storage link placeholder
RUN php artisan storage:link || true

# Note: config/route/view/event caching is done by pam:install command
# This allows environment variables to be properly set at runtime

# Set proper permissions
RUN chown -R www-data:www-data ${WORKDIR} \
    && chmod -R 775 ${WORKDIR}/storage \
    && chmod -R 775 ${WORKDIR}/bootstrap/cache

# Expose port
EXPOSE ${API_PORT}

# Health check is configured in docker-compose.yml for flexibility
# Default: curl -f http://localhost/up || exit 1

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
