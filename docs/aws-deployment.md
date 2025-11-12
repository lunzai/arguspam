# AWS Deployment Guide for ArgusPAM on Ubuntu 22.04 (t4g.medium)

This guide provides step-by-step instructions for deploying ArgusPAM on an AWS t4g.medium instance (4GB RAM, 2 vCPU ARM64) running Ubuntu 22.04 LTS without Docker.

## Table of Contents
- [Prerequisites](#prerequisites)
- [Server Specifications](#server-specifications)
- [Step 1: Initial Server Setup](#step-1-initial-server-setup--security)
- [Step 2: Install MySQL](#step-2-install-and-configure-mysql)
- [Step 3: Install Redis](#step-3-install-and-configure-redis)
- [Step 4: Install PHP](#step-4-install-php-83-with-extensions)
- [Step 5: Install Nginx](#step-5-install-nginx)
- [Step 6: Install Composer](#step-6-install-composer)
- [Step 7: Install Node.js](#step-7-install-nodejs)
- [Step 8: Clone Application](#step-8-clone-and-setup-application)
- [Step 9: Database Setup](#step-9-run-database-migrations)
- [Step 10: Nginx Configuration](#step-10-create-nginx-site-configurations)
- [Step 11: Systemd Services](#step-11-create-systemd-services)
- [Step 12: SSL Setup](#step-12-setup-ssl-with-lets-encrypt)
- [Step 13: Log Rotation](#step-13-setup-log-rotation)
- [Step 14: Cron Jobs](#step-14-setup-cron-jobs)
- [Step 15: Security Hardening](#step-15-final-security-hardening)
- [Step 16: Monitoring](#step-16-monitoring-setup)
- [Step 17: Deployment Script](#step-17-deployment-script)
- [Maintenance](#maintenance-commands)

---

## Prerequisites

- AWS t4g.medium instance (4GB RAM, 2 vCPU ARM64)
- Ubuntu 22.04 LTS
- Root or sudo access
- Domain name with DNS configured:
  - `yourdomain.com` → server IP
  - `api.yourdomain.com` → server IP
- GitHub account with SSH key configured (for private repos)

---

## Server Specifications

### AWS t4g.medium Instance
- **CPU:** 2 vCPUs (ARM64 - AWS Graviton2)
- **RAM:** 4 GB
- **Storage:** 30+ GB SSD (EBS gp3 recommended)
- **Network:** Up to 5 Gbps

### Expected Capacity
- **Concurrent Users:** 200-1000 users
- **Team Size:** 20-100 people
- **Request Rate:** ~500-2000 requests/minute
- **Database Size:** Up to 50GB

### Monthly Cost Estimate
- **Instance:** ~$30/month (t4g.medium)
- **Storage:** ~$3-5/month (30GB EBS gp3)
- **Transfer:** ~$5-10/month (varies)
- **Total:** ~$40-50/month

---

## Step 1: Initial Server Setup & Security

### Connect to Your Instance

```bash
# SSH into your AWS instance
ssh -i your-key.pem ubuntu@your-server-ip
```

### Update System

```bash
# Update all packages
sudo apt update && sudo apt upgrade -y

# Install essential tools
sudo apt install -y software-properties-common curl wget git unzip zip \
    build-essential htop vim nano
```

### Configure Timezone

```bash
# Set timezone (adjust as needed)
sudo timedatectl set-timezone UTC

# Verify
timedatectl
```

### Create Swap File

**Important:** Even with 4GB RAM, swap is recommended for safety.

```bash
# Create 4GB swap file
sudo fallocate -l 4G /swapfile
sudo chmod 600 /swapfile
sudo mkswap /swapfile
sudo swapon /swapfile

# Make swap permanent
echo '/swapfile none swap sw 0 0' | sudo tee -a /etc/fstab

# Verify swap is active
free -h
swapon --show
```

### Configure Swap Behavior

```bash
# Reduce swappiness (use swap less aggressively)
echo 'vm.swappiness=10' | sudo tee -a /etc/sysctl.conf
echo 'vm.vfs_cache_pressure=50' | sudo tee -a /etc/sysctl.conf

# Apply immediately
sudo sysctl -p
```

### Configure Firewall

```bash
# Install and configure UFW
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw --force enable

# Verify firewall status
sudo ufw status
```

### Configure System Limits

```bash
# Increase file descriptor limits
sudo nano /etc/security/limits.conf
```

Add these lines:
```
* soft nofile 65535
* hard nofile 65535
* soft nproc 65535
* hard nproc 65535
www-data soft nofile 65535
www-data hard nofile 65535
```

Apply kernel parameters:
```bash
sudo nano /etc/sysctl.conf
```

Add/update these values:
```
# Network optimization
net.core.somaxconn = 65535
net.ipv4.tcp_max_syn_backlog = 8192
net.ipv4.tcp_tw_reuse = 1
net.ipv4.ip_local_port_range = 10000 65535

# File system
fs.file-max = 2097152

# Memory
vm.swappiness = 10
vm.vfs_cache_pressure = 50
```

Apply changes:
```bash
sudo sysctl -p
```

---

## Step 2: Install and Configure MySQL

### Install MySQL 8.0

```bash
sudo apt install -y mysql-server

# Check MySQL is running
sudo systemctl status mysql
```

### Secure MySQL Installation

```bash
sudo mysql_secure_installation
```

Follow the prompts:
- Set root password: **Yes** (use a strong password)
- Remove anonymous users: **Yes**
- Disallow root login remotely: **Yes**
- Remove test database: **Yes**
- Reload privilege tables: **Yes**

**⚠️ Troubleshooting: If you don't see password options**

MySQL 8.0 on Ubuntu 22.04 uses `auth_socket` by default. If `mysql_secure_installation` doesn't prompt for password:

1. Exit the current process (Ctrl+C)
2. Login to MySQL and set root password manually:

```bash
sudo mysql
```

In MySQL console:
```sql
-- Set root password
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'your_strong_password_here';
FLUSH PRIVILEGES;
EXIT;
```

3. Run `mysql_secure_installation` again:
```bash
sudo mysql_secure_installation
```

Now you'll be able to enter the password and complete the setup.

### Create Database and User

```bash
# Login to MySQL
sudo mysql -u root -p
```

In MySQL console:
```sql
-- Create database
CREATE DATABASE arguspam CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user (replace with a strong password)
CREATE USER 'arguspam'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD_HERE';

-- Grant privileges
GRANT ALL PRIVILEGES ON arguspam.* TO 'arguspam'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;

-- Verify
SHOW DATABASES;
SELECT User, Host FROM mysql.user WHERE User='arguspam';

-- Exit
EXIT;
```

### Optimize MySQL for 4GB RAM

```bash
# Backup original configuration
sudo cp /etc/mysql/mysql.conf.d/mysqld.cnf /etc/mysql/mysql.conf.d/mysqld.cnf.backup

# Edit MySQL configuration
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

Replace/add these settings under `[mysqld]`:

```ini
[mysqld]
# Basic Settings
user                    = mysql
pid-file                = /var/run/mysqld/mysqld.pid
socket                  = /var/run/mysqld/mysqld.sock
port                    = 3306
datadir                 = /var/lib/mysql

# Character Set
character-set-server    = utf8mb4
collation-server        = utf8mb4_unicode_ci

# Connection Settings
max_connections         = 100           # Increased for 4GB RAM
max_allowed_packet      = 64M
connect_timeout         = 10
wait_timeout            = 600
interactive_timeout     = 600

# InnoDB Settings (Optimized for 4GB RAM)
innodb_buffer_pool_size = 1G            # 25% of 4GB RAM
innodb_log_file_size    = 256M
innodb_log_buffer_size  = 32M
innodb_flush_method     = O_DIRECT
innodb_flush_log_at_trx_commit = 2
innodb_file_per_table   = 1
innodb_buffer_pool_instances = 1

# InnoDB Performance
innodb_io_capacity      = 2000
innodb_io_capacity_max  = 4000
innodb_read_io_threads  = 4
innodb_write_io_threads = 4

# Table Cache
table_open_cache        = 2000
table_definition_cache  = 2000

# Temp Tables
tmp_table_size          = 64M
max_heap_table_size     = 64M

# Thread Settings
thread_cache_size       = 16
thread_stack            = 256K

# Query Cache (Disabled in MySQL 8.0+, but keeping for reference)
# MySQL 8.0 removed query cache - it's handled by InnoDB buffer pool

# Binary Logging (Enable for backups/replication)
log_bin                 = /var/log/mysql/mysql-bin.log
binlog_expire_logs_seconds = 604800  # 7 days
max_binlog_size         = 100M

# Slow Query Log
slow_query_log          = 1
slow_query_log_file     = /var/log/mysql/mysql-slow.log
long_query_time         = 2

# Error Log
log_error               = /var/log/mysql/error.log

# Performance Schema (Light monitoring)
performance_schema      = ON
```

### Create MySQL Log Directory

```bash
sudo mkdir -p /var/log/mysql
sudo chown mysql:mysql /var/log/mysql
```

### Restart MySQL

```bash
sudo systemctl restart mysql
sudo systemctl enable mysql

# Verify MySQL is running
sudo systemctl status mysql

# Check MySQL variables
sudo mysql -u root -p -e "SHOW VARIABLES LIKE 'innodb_buffer_pool_size';"
```

---

## Step 3: Install and Configure Redis

### Install Redis

```bash
sudo apt install -y redis-server

# Verify installation
redis-cli --version
```

### Configure Redis with Resource Limits

```bash
# Backup original config
sudo cp /etc/redis/redis.conf /etc/redis/redis.conf.backup

# Edit Redis configuration
sudo nano /etc/redis/redis.conf
```

Update these settings:

```ini
# Network
bind 127.0.0.1 ::1
protected-mode yes
port 6379
timeout 300
tcp-keepalive 60

# Memory Management - Critical for 4GB Server
maxmemory 1gb
maxmemory-policy allkeys-lru
maxmemory-samples 5

# Persistence
save 900 1
save 300 10
save 60 10000

# AOF (Append Only File)
appendonly yes
appendfilename "appendonly.aof"
appendfsync everysec

# Performance
databases 16
tcp-backlog 511

# Snapshotting
stop-writes-on-bgsave-error yes
rdbcompression yes
rdbchecksum yes
dbfilename dump.rdb
dir /var/lib/redis

# Security - Disable dangerous commands
rename-command FLUSHDB ""
rename-command FLUSHALL ""
rename-command CONFIG ""
rename-command SHUTDOWN SHUTDOWN_PLEASE

# Logging
loglevel notice
logfile /var/log/redis/redis-server.log

# Slow log
slowlog-log-slower-than 10000
slowlog-max-len 128
```

### Optimize Redis System Settings

```bash
# Disable transparent huge pages (Redis recommendation)
echo never | sudo tee /sys/kernel/mm/transparent_hugepage/enabled
echo never | sudo tee /sys/kernel/mm/transparent_hugepage/defrag

# Make it permanent
sudo nano /etc/rc.local
```

Add before `exit 0`:
```bash
echo never > /sys/kernel/mm/transparent_hugepage/enabled
echo never > /sys/kernel/mm/transparent_hugepage/defrag
```

### Configure Redis Overcommit Memory

```bash
sudo nano /etc/sysctl.conf
```

Add:
```
vm.overcommit_memory = 1
```

Apply:
```bash
sudo sysctl -p
```

### Restart Redis

```bash
sudo systemctl restart redis-server
sudo systemctl enable redis-server

# Verify Redis is running
sudo systemctl status redis-server

# Test Redis connection
redis-cli ping
# Should return: PONG

# Check memory settings
redis-cli CONFIG GET maxmemory
redis-cli INFO memory
```

---

## Step 4: Install PHP 8.3 with Extensions

### Add PHP Repository

```bash
# Add Ondrej's PPA for PHP 8.3
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
```

### Install PHP 8.3 and Extensions

```bash
sudo apt install -y \
    php8.3 \
    php8.3-fpm \
    php8.3-cli \
    php8.3-common \
    php8.3-mysql \
    php8.3-redis \
    php8.3-curl \
    php8.3-gd \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-zip \
    php8.3-bcmath \
    php8.3-intl \
    php8.3-opcache \
    php8.3-readline \
    php8.3-imagick

# Verify installation
php -v
php -m | grep -E 'redis|opcache|mysql'
```

### Optimize PHP-FPM for 4GB RAM

```bash
# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

Update these settings:

```ini
[www]
; Process Manager
user = www-data
group = www-data

; Socket Configuration
listen = /run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
listen.backlog = 511

; Process Manager Settings (optimized for 4GB RAM)
pm = dynamic
pm.max_children = 40              ; Max number of child processes
pm.start_servers = 8              ; Start with 8 processes
pm.min_spare_servers = 4          ; Min idle processes
pm.max_spare_servers = 12         ; Max idle processes
pm.max_requests = 1000            ; Restart after 1000 requests (prevent memory leaks)

; Process Timeouts
pm.process_idle_timeout = 10s
request_terminate_timeout = 60s
request_slowlog_timeout = 5s

; Status and Ping
pm.status_path = /php-fpm-status
ping.path = /php-fpm-ping
ping.response = pong

; Logging
slowlog = /var/log/php8.3-fpm-slow.log
php_admin_value[error_log] = /var/log/php8.3-fpm-error.log
php_admin_flag[log_errors] = on

; Security
php_admin_value[disable_functions] = exec,passthru,shell_exec,system,proc_open,popen
```

### Optimize PHP Configuration

```bash
# Edit PHP-FPM php.ini
sudo nano /etc/php/8.3/fpm/php.ini
```

Update these values:

```ini
; Resource Limits
memory_limit = 256M
max_execution_time = 60
max_input_time = 60
max_input_vars = 3000

; File Uploads
upload_max_filesize = 64M
post_max_size = 64M
file_uploads = On

; Error Handling (Production)
display_errors = Off
display_startup_errors = Off
log_errors = On
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
error_log = /var/log/php8.3-fpm-error.log

; Session Configuration
session.gc_maxlifetime = 1440
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
session.cookie_samesite = "Lax"

; Security
expose_php = Off
allow_url_fopen = On
allow_url_include = Off

; OPcache Settings (Critical for Performance)
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=256          ; Increased for 4GB RAM
opcache.interned_strings_buffer=32      ; Increased
opcache.max_accelerated_files=20000     ; Increased
opcache.max_wasted_percentage=10
opcache.validate_timestamps=0           ; Disable in production
opcache.revalidate_freq=0
opcache.save_comments=1
opcache.fast_shutdown=1
opcache.enable_file_override=1
opcache.file_cache=/var/cache/php8.3/opcache
opcache.file_cache_consistency_checks=0

; Realpath Cache (Improves file path resolution)
realpath_cache_size=4096K
realpath_cache_ttl=600

; Date
date.timezone = UTC
```

### Create OPcache Directory

```bash
sudo mkdir -p /var/cache/php8.3/opcache
sudo chown -R www-data:www-data /var/cache/php8.3
```

### Update PHP CLI Configuration

```bash
sudo nano /etc/php/8.3/cli/php.ini
```

Update:
```ini
memory_limit = 512M
max_execution_time = 0
```

### Restart PHP-FPM

```bash
sudo systemctl restart php8.3-fpm
sudo systemctl enable php8.3-fpm

# Verify PHP-FPM is running
sudo systemctl status php8.3-fpm

# Check PHP-FPM processes
ps aux | grep php-fpm
```

---

## Step 5: Install Nginx

### Install Nginx

```bash
sudo apt install -y nginx

# Verify installation
nginx -v
```

### Optimize Nginx for 4GB RAM

```bash
# Backup original configuration
sudo cp /etc/nginx/nginx.conf /etc/nginx/nginx.conf.backup

# Edit main Nginx configuration
sudo nano /etc/nginx/nginx.conf
```

Replace with optimized configuration:

```nginx
user www-data;
worker_processes 2;              # Match CPU cores (2 vCPUs)
worker_rlimit_nofile 65535;      # Max open files per worker
pid /run/nginx.pid;
error_log /var/log/nginx/error.log warn;

events {
    worker_connections 8192;      # 2 workers × 8192 = 16384 total
    use epoll;                    # Efficient on Linux
    multi_accept on;
}

http {
    # MIME Types
    include /etc/nginx/mime.types;
    default_type application/octet-stream;
    
    # Logging Format
    log_format main '$remote_addr - $remote_user [$time_local] "$request" '
                    '$status $body_bytes_sent "$http_referer" '
                    '"$http_user_agent" "$http_x_forwarded_for" '
                    'rt=$request_time uct="$upstream_connect_time" '
                    'uht="$upstream_header_time" urt="$upstream_response_time"';
    
    access_log /var/log/nginx/access.log main buffer=32k flush=5s;
    
    # Performance Settings
    sendfile on;
    tcp_nopush on;
    tcp_nodelay on;
    keepalive_timeout 30;
    keepalive_requests 100;
    reset_timedout_connection on;
    
    # Timeouts
    client_body_timeout 12;
    client_header_timeout 12;
    send_timeout 10;
    
    # Buffer Sizes (optimized for 4GB RAM)
    client_body_buffer_size 256k;
    client_max_body_size 64M;
    client_header_buffer_size 2k;
    large_client_header_buffers 4 16k;
    output_buffers 2 32k;
    postpone_output 1460;
    
    # FastCGI Buffers
    fastcgi_buffers 16 16k;
    fastcgi_buffer_size 32k;
    fastcgi_busy_buffers_size 64k;
    fastcgi_temp_file_write_size 256k;
    
    # Proxy Buffers
    proxy_buffers 16 16k;
    proxy_buffer_size 32k;
    proxy_busy_buffers_size 64k;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_min_length 1000;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/json
        application/javascript
        application/xml+rss
        application/rss+xml
        application/atom+xml
        image/svg+xml
        application/vnd.ms-fontobject
        font/truetype
        font/opentype
        application/x-font-ttf;
    gzip_disable "msie6";
    
    # Brotli Compression (if module available)
    # brotli on;
    # brotli_comp_level 6;
    # brotli_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
    
    # Open File Cache
    open_file_cache max=20000 inactive=30s;
    open_file_cache_valid 60s;
    open_file_cache_min_uses 2;
    open_file_cache_errors on;
    
    # Rate Limiting Zones
    limit_req_zone $binary_remote_addr zone=api_limit:10m rate=20r/s;
    limit_req_zone $binary_remote_addr zone=web_limit:10m rate=30r/s;
    limit_req_zone $binary_remote_addr zone=login_limit:10m rate=5r/m;
    limit_conn_zone $binary_remote_addr zone=addr:10m;
    
    # Connection Limiting
    limit_conn addr 20;
    
    # Security Headers (Global)
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    
    # Hide Nginx Version
    server_tokens off;
    
    # Include Virtual Hosts
    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
}
```

### Remove Default Site

```bash
sudo rm /etc/nginx/sites-enabled/default
```

### Test and Restart Nginx

```bash
# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx

# Verify Nginx is running
sudo systemctl status nginx
```

---

## Step 6: Install Composer

```bash
# Download Composer installer
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php

# Verify installer (optional but recommended)
HASH="$(curl -sS https://composer.github.io/installer.sig)"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"

# Install Composer globally
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Clean up
rm composer-setup.php

# Verify installation
composer --version
```

---

## Step 7: Install Node.js

### Install Node.js 20 LTS (ARM64 compatible)

```bash
# Add NodeSource repository for Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -

# Install Node.js
sudo apt install -y nodejs

# Verify installation
node --version
npm --version
```

### Configure NPM

```bash
# Set NPM cache location
mkdir -p ~/.npm-global
npm config set prefix '~/.npm-global'

# Add to PATH
echo 'export PATH=~/.npm-global/bin:$PATH' >> ~/.bashrc
source ~/.bashrc
```

---

## Step 8: Clone and Setup Application

### Setup SSH Key for GitHub (if using private repo)

```bash
# Generate SSH key
ssh-keygen -t ed25519 -C "your-email@example.com"

# Display public key
cat ~/.ssh/id_ed25519.pub

# Add this key to your GitHub account at:
# https://github.com/settings/keys
```

### Setup SSH Agent with Keychain (Persist Across Sessions)

Install and configure keychain to manage SSH keys permanently:

```bash
# Install keychain
sudo apt install -y keychain

# Configure keychain to auto-start
echo '' >> ~/.bashrc
echo '# SSH Agent with Keychain' >> ~/.bashrc
echo 'if [ -x /usr/bin/keychain ]; then' >> ~/.bashrc
echo '    eval $(keychain --eval --agents ssh id_ed25519)' >> ~/.bashrc
echo 'fi' >> ~/.bashrc

# Load the configuration
source ~/.bashrc

# Add your SSH key (will prompt for passphrase if key is encrypted)
ssh-add ~/.ssh/id_ed25519

# Verify key is added
ssh-add -l

# Test GitHub connection
ssh -T git@github.com
```

**Benefits of Keychain:**
- ✅ SSH keys persist across all terminal sessions
- ✅ Survives server reboots
- ✅ Works with cron jobs and automated tasks
- ✅ Only need to enter passphrase once after reboot

**Troubleshooting:**

```bash
# Check if keychain is working
keychain --list

# Check SSH agent status
ssh-add -l

# If issues, restart keychain
pkill ssh-agent
source ~/.bashrc
ssh-add ~/.ssh/id_ed25519
```

### Create Application Directory

```bash
# Create directory
sudo mkdir -p /var/www
cd /var/www

# Clone repository (replace with your repo URL)
sudo git clone git@github.com:yourusername/arguspam.git

# Alternative: using HTTPS
# sudo git clone https://github.com/yourusername/arguspam.git

# Set ownership
sudo chown -R www-data:www-data arguspam
cd arguspam
```

### Setup API (Laravel)

```bash
# Switch to www-data user for setup
sudo -u www-data bash
cd /var/www/arguspam/api

# Install Composer dependencies (production)
composer install --no-dev --optimize-autoloader --no-interaction

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Exit www-data shell
exit
```

### Configure API Environment

```bash
# Edit .env file
sudo nano /var/www/arguspam/api/.env
```

Update with your configuration:

```ini
# Application
APP_NAME=ArgusPAM
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=arguspam
DB_USERNAME=arguspam
DB_PASSWORD=YOUR_MYSQL_PASSWORD_HERE

# Cache & Queue
CACHE_DRIVER=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CLIENT=predis

# URLs & CORS
APP_WEB_URL=https://yourdomain.com
WEB_ORIGIN=https://yourdomain.com
PUBLIC_API_URL=https://api.yourdomain.com
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
CORS_ALLOWED_ORIGINS=https://yourdomain.com
SESSION_DOMAIN=.yourdomain.com

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# OpenAI
OPENAI_API_KEY=your-openai-api-key-here
OPENAI_ORGANIZATION=
OPENAI_MODEL=gpt-4o-mini

# Admin Emails
EMAIL_DEFAULT=admin@yourdomain.com
EMAIL_SUPPORT=support@yourdomain.com

# Optional Services
# SLACK_BOT_USER_OAUTH_TOKEN=
# MAXMIND_USER_ID=
# MAXMIND_LICENSE_KEY=

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=info

# Telescope (disable in production for better performance)
TELESCOPE_ENABLED=false
```

### Setup Web (SvelteKit)

```bash
cd /var/www/arguspam/web

# Install dependencies
sudo -u www-data npm install

# Create .env file
sudo -u www-data nano .env
```

Add:
```ini
PUBLIC_API_URL=https://api.yourdomain.com
ORIGIN=https://yourdomain.com
```

Build the application:
```bash
sudo -u www-data npm run build
```

### Set Proper Permissions

```bash
cd /var/www/arguspam

# Set ownership
sudo chown -R www-data:www-data .

# Set directory permissions
sudo find . -type d -exec chmod 755 {} \;

# Set file permissions
sudo find . -type f -exec chmod 644 {} \;

# Storage and cache directories need write access
sudo chmod -R 775 api/storage
sudo chmod -R 775 api/bootstrap/cache
```

---

## Step 9: Run Database Migrations

```bash
cd /var/www/arguspam/api

# Run migrations
sudo -u www-data php artisan migrate --force

# Run seeders (if available)
sudo -u www-data php artisan db:seed --force

# Create storage link
sudo -u www-data php artisan storage:link

# Cache configuration
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache

# Run installation command (creates first organization and admin)
sudo -u www-data php artisan pam:install
```

---

## Step 10: Create Nginx Site Configurations

### API Configuration (api.yourdomain.com)

```bash
sudo nano /etc/nginx/sites-available/arguspam-api
```

```nginx
# API Server Configuration
server {
    listen 80;
    listen [::]:80;
    server_name api.yourdomain.com;
    
    root /var/www/arguspam/api/public;
    index index.php;
    
    # Logging
    access_log /var/log/nginx/arguspam-api-access.log main;
    error_log /var/log/nginx/arguspam-api-error.log warn;
    
    # Rate Limiting
    limit_req zone=api_limit burst=40 nodelay;
    limit_conn addr 15;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    
    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP-FPM processing
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Hide PHP version
        fastcgi_hide_header X-Powered-By;
        
        # Timeouts
        fastcgi_read_timeout 60s;
        fastcgi_send_timeout 60s;
        fastcgi_connect_timeout 10s;
        
        # Buffers
        fastcgi_buffer_size 32k;
        fastcgi_buffers 8 16k;
        fastcgi_busy_buffers_size 64k;
        fastcgi_temp_file_write_size 256k;
        
        # PHP settings
        fastcgi_param PHP_VALUE "upload_max_filesize=64M \n post_max_size=64M";
    }
    
    # Deny access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }
    
    # Health check endpoint
    location /up {
        access_log off;
        return 200 "OK\n";
        add_header Content-Type text/plain;
    }
    
    # PHP-FPM status (optional, for monitoring)
    location ~ ^/(php-fpm-status|php-fpm-ping)$ {
        access_log off;
        allow 127.0.0.1;
        deny all;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
    
    # Disable logging for static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
```

### Web Configuration (yourdomain.com)

```bash
sudo nano /etc/nginx/sites-available/arguspam-web
```

```nginx
# SvelteKit Upstream
upstream sveltekit {
    server 127.0.0.1:3000;
    keepalive 64;
}

# Web Server Configuration
server {
    listen 80;
    listen [::]:80;
    server_name yourdomain.com www.yourdomain.com;
    
    # Logging
    access_log /var/log/nginx/arguspam-web-access.log main;
    error_log /var/log/nginx/arguspam-web-error.log warn;
    
    # Rate Limiting
    limit_req zone=web_limit burst=60 nodelay;
    limit_conn addr 30;
    
    # Redirect www to non-www
    if ($host = 'www.yourdomain.com') {
        return 301 $scheme://yourdomain.com$request_uri;
    }
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    
    # Proxy to SvelteKit
    location / {
        proxy_pass http://sveltekit;
        proxy_http_version 1.1;
        
        # Headers
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Host $host;
        proxy_set_header X-Forwarded-Port $server_port;
        
        # WebSocket support
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        
        # Timeouts
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
        
        # Buffering
        proxy_buffering on;
        proxy_buffer_size 4k;
        proxy_buffers 8 4k;
        proxy_busy_buffers_size 8k;
        
        # Disable buffering for SSE/streaming
        proxy_cache off;
        proxy_buffering off;
    }
    
    # Health check
    location /health {
        access_log off;
        return 200 "OK\n";
        add_header Content-Type text/plain;
    }
    
    # Robots.txt
    location = /robots.txt {
        access_log off;
        log_not_found off;
    }
    
    # Favicon
    location = /favicon.ico {
        access_log off;
        log_not_found off;
    }
}
```

### Enable Sites

```bash
# Create symbolic links
sudo ln -s /etc/nginx/sites-available/arguspam-api /etc/nginx/sites-enabled/
sudo ln -s /etc/nginx/sites-available/arguspam-web /etc/nginx/sites-enabled/

# Test Nginx configuration
sudo nginx -t

# Reload Nginx
sudo systemctl reload nginx
```

---

## Step 11: Create Systemd Services

### Laravel Horizon Service (Queue Worker)

```bash
sudo nano /etc/systemd/system/arguspam-horizon.service
```

```ini
[Unit]
Description=ArgusPAM Laravel Horizon Queue Worker
After=network.target mysql.service redis-server.service

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=5
ExecStart=/usr/bin/php /var/www/arguspam/api/artisan horizon
WorkingDirectory=/var/www/arguspam/api

# Resource Limits (for 4GB RAM server)
MemoryLimit=512M
CPUQuota=100%

# Logging
StandardOutput=journal
StandardError=journal
SyslogIdentifier=arguspam-horizon

[Install]
WantedBy=multi-user.target
```

### SvelteKit Web Service

```bash
sudo nano /etc/systemd/system/arguspam-web.service
```

```ini
[Unit]
Description=ArgusPAM SvelteKit Web Application
After=network.target

[Service]
Type=simple
User=www-data
Group=www-data
Restart=always
RestartSec=5

# Environment Variables
Environment="NODE_ENV=production"
Environment="PORT=3000"
Environment="HOST=127.0.0.1"
Environment="ORIGIN=https://yourdomain.com"
Environment="PUBLIC_API_URL=https://api.yourdomain.com"
Environment="BODY_SIZE_LIMIT=Infinity"

# Execution
ExecStart=/usr/bin/node /var/www/arguspam/web/build/index.js
WorkingDirectory=/var/www/arguspam/web

# Resource Limits (for 4GB RAM server)
MemoryLimit=1G
CPUQuota=150%

# Logging
StandardOutput=journal
StandardError=journal
SyslogIdentifier=arguspam-web

[Install]
WantedBy=multi-user.target
```

### Enable and Start Services

```bash
# Reload systemd
sudo systemctl daemon-reload

# Enable services (start on boot)
sudo systemctl enable arguspam-horizon
sudo systemctl enable arguspam-web

# Start services
sudo systemctl start arguspam-horizon
sudo systemctl start arguspam-web

# Check service status
sudo systemctl status arguspam-horizon
sudo systemctl status arguspam-web

# View logs
sudo journalctl -u arguspam-horizon -f
sudo journalctl -u arguspam-web -f
```

---

## Step 12: Setup SSL with Let's Encrypt

### Install Certbot

```bash
# Install Certbot with Nginx plugin
sudo apt install -y certbot python3-certbot-nginx
```

### Obtain SSL Certificates

```bash
# Obtain certificates for both domains
sudo certbot --nginx \
    -d yourdomain.com \
    -d www.yourdomain.com \
    -d api.yourdomain.com \
    --non-interactive \
    --agree-tos \
    --email your-email@example.com \
    --redirect

# Alternative: Obtain certificates separately
# sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
# sudo certbot --nginx -d api.yourdomain.com
```

### Verify SSL Configuration

```bash
# Test SSL certificates
sudo certbot certificates

# Test SSL renewal (dry run)
sudo certbot renew --dry-run
```

### Setup Auto-Renewal

```bash
# Certbot should already have a systemd timer
sudo systemctl status certbot.timer

# Enable the timer if not already enabled
sudo systemctl enable certbot.timer
sudo systemctl start certbot.timer

# Verify renewal timer
sudo systemctl list-timers | grep certbot
```

### Test Your SSL

Visit your domains:
- `https://yourdomain.com`
- `https://api.yourdomain.com`

Test SSL configuration:
- [SSL Labs Test](https://www.ssllabs.com/ssltest/)

---

## Step 13: Setup Log Rotation

### Create Log Rotation Configuration

```bash
sudo nano /etc/logrotate.d/arguspam
```

```
# Laravel Application Logs
/var/www/arguspam/api/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
    postrotate
        systemctl reload php8.3-fpm > /dev/null 2>&1 || true
    endscript
}

# Nginx ArgusPAM Logs
/var/log/nginx/arguspam-*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data adm
    sharedscripts
    postrotate
        systemctl reload nginx > /dev/null 2>&1 || true
    endscript
}

# PHP-FPM Logs
/var/log/php8.3-fpm*.log {
    daily
    missingok
    rotate 7
    compress
    delaycompress
    notifempty
    create 0640 www-data adm
    sharedscripts
    postrotate
        systemctl reload php8.3-fpm > /dev/null 2>&1 || true
    endscript
}
```

### Test Log Rotation

```bash
# Test logrotate configuration
sudo logrotate -d /etc/logrotate.d/arguspam

# Force log rotation
sudo logrotate -f /etc/logrotate.d/arguspam
```

---

## Step 14: Setup Cron Jobs

### Configure Laravel Scheduler

```bash
# Edit crontab for www-data user
sudo crontab -u www-data -e
```

Add this line:
```
* * * * * cd /var/www/arguspam/api && php artisan schedule:run >> /dev/null 2>&1
```

### Verify Cron Job

```bash
# List www-data cron jobs
sudo crontab -u www-data -l

# Monitor cron execution
sudo tail -f /var/log/syslog | grep CRON
```

---

## Step 15: Final Security Hardening

### Install Fail2Ban

```bash
# Install Fail2Ban
sudo apt install -y fail2ban

# Copy default configuration
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
```

### Configure Fail2Ban

```bash
sudo nano /etc/fail2ban/jail.local
```

Update/add these sections:

```ini
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5
destemail = your-email@example.com
sendername = Fail2Ban
action = %(action_mwl)s

[sshd]
enabled = true
port = ssh
logpath = /var/log/auth.log
maxretry = 3

[nginx-http-auth]
enabled = true
filter = nginx-http-auth
port = http,https
logpath = /var/log/nginx/*error.log

[nginx-limit-req]
enabled = true
filter = nginx-limit-req
port = http,https
logpath = /var/log/nginx/*error.log
maxretry = 10

[nginx-botsearch]
enabled = true
filter = nginx-botsearch
port = http,https
logpath = /var/log/nginx/*access.log
maxretry = 2
```

### Start Fail2Ban

```bash
sudo systemctl enable fail2ban
sudo systemctl start fail2ban

# Check Fail2Ban status
sudo fail2ban-client status
sudo fail2ban-client status sshd
sudo fail2ban-client status nginx-limit-req
```

### Configure SSH Security

```bash
sudo nano /etc/ssh/sshd_config
```

Update these settings:
```
PermitRootLogin no
PasswordAuthentication no
PubkeyAuthentication yes
X11Forwarding no
MaxAuthTries 3
ClientAliveInterval 300
ClientAliveCountMax 2
```

Restart SSH:
```bash
sudo systemctl restart sshd
```

### Setup Unattended Upgrades

```bash
# Install unattended-upgrades
sudo apt install -y unattended-upgrades

# Configure automatic security updates
sudo dpkg-reconfigure -plow unattended-upgrades
```

Edit configuration:
```bash
sudo nano /etc/apt/apt.conf.d/50unattended-upgrades
```

Ensure these lines are uncommented:
```
"${distro_id}:${distro_codename}-security";
"${distro_id}:${distro_codename}-updates";
```

---

## Step 16: Monitoring Setup

### Create System Monitoring Script

```bash
sudo nano /usr/local/bin/arguspam-monitor.sh
```

```bash
#!/bin/bash

echo "========================================="
echo "  ArgusPAM System Status"
echo "  $(date)"
echo "========================================="
echo ""

echo "=== Disk Usage ==="
df -h | grep -E '/$|/var'
echo ""

echo "=== Memory Usage ==="
free -h
echo ""

echo "=== Swap Usage ==="
swapon --show
echo ""

echo "=== CPU Load ==="
uptime
echo ""

echo "=== Service Status ==="
services=("mysql" "redis-server" "php8.3-fpm" "nginx" "arguspam-horizon" "arguspam-web")
for service in "${services[@]}"; do
    if systemctl is-active --quiet "$service"; then
        echo "✓ $service: Running"
    else
        echo "✗ $service: Stopped"
    fi
done
echo ""

echo "=== PHP-FPM Processes ==="
ps aux | grep php-fpm | grep -v grep | wc -l
echo ""

echo "=== Nginx Connections ==="
netstat -an | grep :80 | wc -l
echo ""

echo "=== Redis Memory ==="
redis-cli INFO memory | grep used_memory_human
echo ""

echo "=== MySQL Connections ==="
mysql -u arguspam -p"YOUR_PASSWORD" -e "SHOW STATUS WHERE Variable_name = 'Threads_connected';" 2>/dev/null || echo "Unable to connect to MySQL"
echo ""

echo "=== Recent Errors (last 10) ==="
journalctl -p err -n 10 --no-pager --since "1 hour ago"
echo ""

echo "=== Disk I/O ==="
iostat -x 1 2 | tail -n +4
echo ""

echo "========================================="
```

```bash
sudo chmod +x /usr/local/bin/arguspam-monitor.sh
```

### Create Health Check Script

```bash
sudo nano /usr/local/bin/arguspam-health.sh
```

```bash
#!/bin/bash

# Health check script
check_service() {
    if systemctl is-active --quiet "$1"; then
        return 0
    else
        return 1
    fi
}

check_http() {
    if curl -sf "$1" > /dev/null; then
        return 0
    else
        return 1
    fi
}

failed=0

# Check services
for service in mysql redis-server php8.3-fpm nginx arguspam-horizon arguspam-web; do
    if ! check_service "$service"; then
        echo "CRITICAL: $service is not running"
        failed=1
    fi
done

# Check HTTP endpoints
if ! check_http "http://localhost/health"; then
    echo "CRITICAL: Web endpoint is not responding"
    failed=1
fi

if ! check_http "http://localhost:8080/up"; then
    echo "CRITICAL: API endpoint is not responding"
    failed=1
fi

# Check disk space
disk_usage=$(df / | tail -1 | awk '{print $5}' | sed 's/%//')
if [ "$disk_usage" -gt 85 ]; then
    echo "WARNING: Disk usage is at ${disk_usage}%"
    failed=1
fi

# Check memory
mem_usage=$(free | grep Mem | awk '{print int($3/$2 * 100)}')
if [ "$mem_usage" -gt 90 ]; then
    echo "WARNING: Memory usage is at ${mem_usage}%"
    failed=1
fi

if [ $failed -eq 0 ]; then
    echo "OK: All checks passed"
    exit 0
else
    exit 1
fi
```

```bash
sudo chmod +x /usr/local/bin/arguspam-health.sh
```

### Setup Monitoring Cron

```bash
sudo crontab -e
```

Add:
```
# Health check every 5 minutes
*/5 * * * * /usr/local/bin/arguspam-health.sh >> /var/log/arguspam-health.log 2>&1

# Daily system report at 8 AM
0 8 * * * /usr/local/bin/arguspam-monitor.sh | mail -s "ArgusPAM Daily Report" your-email@example.com
```

---

## Step 17: Deployment Script

### Create Deployment Script

```bash
sudo nano /usr/local/bin/arguspam-deploy.sh
```

```bash
#!/bin/bash
set -e

echo "========================================="
echo "  ArgusPAM Deployment Script"
echo "========================================="
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    print_error "Please run as root or with sudo"
    exit 1
fi

echo "Step 1: Pulling latest code..."
cd /var/www/arguspam
sudo -u www-data git pull origin main || {
    print_error "Failed to pull latest code"
    exit 1
}
print_success "Code updated"

echo ""
echo "Step 2: Updating API dependencies..."
cd api
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction || {
    print_error "Failed to install composer dependencies"
    exit 1
}
print_success "Composer dependencies updated"

echo ""
echo "Step 3: Running database migrations..."
sudo -u www-data php artisan migrate --force || {
    print_error "Failed to run migrations"
    exit 1
}
print_success "Migrations completed"

echo ""
echo "Step 4: Clearing and caching configuration..."
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache
sudo -u www-data php artisan event:cache
print_success "Configuration cached"

echo ""
echo "Step 5: Updating Web dependencies..."
cd ../web
sudo -u www-data npm install || {
    print_error "Failed to install npm dependencies"
    exit 1
}
print_success "NPM dependencies updated"

echo ""
echo "Step 6: Building Web application..."
sudo -u www-data npm run build || {
    print_error "Failed to build web application"
    exit 1
}
print_success "Web application built"

echo ""
echo "Step 7: Restarting services..."
systemctl restart arguspam-horizon
systemctl restart arguspam-web
systemctl reload php8.3-fpm
systemctl reload nginx
print_success "Services restarted"

echo ""
echo "Step 8: Running health checks..."
sleep 5
if /usr/local/bin/arguspam-health.sh; then
    print_success "Health checks passed"
else
    print_warning "Some health checks failed - please investigate"
fi

echo ""
print_success "Deployment completed successfully!"
echo ""
```

```bash
sudo chmod +x /usr/local/bin/arguspam-deploy.sh
```

### Create Rollback Script

```bash
sudo nano /usr/local/bin/arguspam-rollback.sh
```

```bash
#!/bin/bash
set -e

echo "ArgusPAM Rollback Script"
echo ""

if [ -z "$1" ]; then
    echo "Usage: $0 <commit-hash>"
    exit 1
fi

COMMIT=$1

echo "Rolling back to commit: $COMMIT"
cd /var/www/arguspam
sudo -u www-data git reset --hard "$COMMIT"

echo "Updating dependencies..."
cd api
sudo -u www-data composer install --no-dev --optimize-autoloader --no-interaction

cd ../web
sudo -u www-data npm install
sudo -u www-data npm run build

echo "Restarting services..."
sudo systemctl restart arguspam-horizon
sudo systemctl restart arguspam-web
sudo systemctl reload php8.3-fpm
sudo systemctl reload nginx

echo "Rollback completed!"
```

```bash
sudo chmod +x /usr/local/bin/arguspam-rollback.sh
```

---

## Maintenance Commands

### Useful Day-to-Day Commands

```bash
# View all services status
sudo systemctl status mysql redis-server php8.3-fpm nginx arguspam-horizon arguspam-web

# Restart all services
sudo systemctl restart mysql redis-server php8.3-fpm nginx arguspam-horizon arguspam-web

# View application logs
sudo tail -f /var/www/arguspam/api/storage/logs/laravel.log

# View Horizon logs
sudo journalctl -u arguspam-horizon -f

# View Web logs
sudo journalctl -u arguspam-web -f

# View Nginx error logs
sudo tail -f /var/log/nginx/arguspam-api-error.log
sudo tail -f /var/log/nginx/arguspam-web-error.log

# View Nginx access logs
sudo tail -f /var/log/nginx/arguspam-api-access.log
sudo tail -f /var/log/nginx/arguspam-web-access.log

# Check Redis memory usage
redis-cli INFO memory

# Check MySQL processlist
sudo mysql -u root -p -e "SHOW FULL PROCESSLIST;"

# Monitor system resources
htop

# Check disk usage
df -h

# Check memory usage
free -h

# View system resource usage
sudo /usr/local/bin/arguspam-monitor.sh

# Run health checks
sudo /usr/local/bin/arguspam-health.sh

# Clear Laravel cache
cd /var/www/arguspam/api
sudo -u www-data php artisan cache:clear
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan route:clear
sudo -u www-data php artisan view:clear

# Clear OPcache
sudo systemctl reload php8.3-fpm

# View queue jobs
cd /var/www/arguspam/api
sudo -u www-data php artisan horizon:status
sudo -u www-data php artisan queue:failed

# Retry failed queue jobs
sudo -u www-data php artisan queue:retry all

# Check SSL certificate expiration
sudo certbot certificates
```

### Database Backup

```bash
# Create backup script
sudo nano /usr/local/bin/arguspam-backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/arguspam"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="arguspam"
DB_USER="arguspam"
DB_PASS="YOUR_MYSQL_PASSWORD"

# Create backup directory
mkdir -p "$BACKUP_DIR"

# Backup database
mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/db_${DATE}.sql.gz"

# Backup storage
tar -czf "$BACKUP_DIR/storage_${DATE}.tar.gz" /var/www/arguspam/api/storage

# Delete backups older than 7 days
find "$BACKUP_DIR" -name "*.gz" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
sudo chmod +x /usr/local/bin/arguspam-backup.sh

# Add to crontab (daily at 2 AM)
sudo crontab -e
```

Add:
```
0 2 * * * /usr/local/bin/arguspam-backup.sh >> /var/log/arguspam-backup.log 2>&1
```

### Performance Optimization Tips

1. **Monitor Swap Usage**
   - If swap is heavily used, consider upgrading to t4g.large
   ```bash
   watch -n 1 free -h
   ```

2. **Monitor PHP-FPM Processes**
   - Adjust `pm.max_children` if needed
   ```bash
   watch -n 2 'ps aux | grep php-fpm | grep -v grep | wc -l'
   ```

3. **Monitor MySQL Performance**
   ```bash
   sudo mysql -u root -p -e "SHOW STATUS WHERE Variable_name LIKE '%Thread%' OR Variable_name LIKE '%Connect%';"
   ```

4. **Check Slow Queries**
   ```bash
   sudo tail -f /var/log/mysql/mysql-slow.log
   ```

5. **Monitor Redis Memory**
   ```bash
   watch -n 2 'redis-cli INFO memory | grep used_memory_human'
   ```

6. **Check Nginx Performance**
   ```bash
   # Active connections
   watch -n 1 'netstat -an | grep :80 | wc -l'
   ```

### Troubleshooting

#### Services Not Starting

```bash
# Check service logs
sudo journalctl -u arguspam-horizon -n 50
sudo journalctl -u arguspam-web -n 50

# Check PHP-FPM errors
sudo tail -100 /var/log/php8.3-fpm-error.log

# Check Nginx errors
sudo tail -100 /var/log/nginx/error.log
```

#### PHP-FPM Configuration Errors

If PHP-FPM fails to start with exit code 78:

```bash
# Test PHP-FPM configuration
sudo php-fpm8.3 -t

# View detailed error
sudo journalctl -u php8.3-fpm.service -n 100 --no-pager
```

**Common Issues:**

1. **Invalid `disable_functions` in pool config:**
   ```bash
   sudo nano /etc/php/8.3/fpm/pool.d/www.conf
   ```
   Comment out or remove the `php_admin_value[disable_functions]` line.

2. **Missing directories:**
   ```bash
   sudo mkdir -p /var/cache/php8.3/opcache
   sudo chown -R www-data:www-data /var/cache/php8.3
   sudo touch /var/log/php8.3-fpm-error.log
   sudo chown www-data:www-data /var/log/php8.3-fpm*.log
   ```

3. **After fixing, restart:**
   ```bash
   sudo systemctl start php8.3-fpm
   sudo systemctl status php8.3-fpm
   ```

#### Nginx Configuration Errors

**Error: "zero size shared memory zone"**

This means `limit_conn_zone` is missing the size parameter:

```bash
# Test Nginx configuration
sudo nginx -t

# Edit main config
sudo nano /etc/nginx/nginx.conf
```

Ensure the zone has a size (e.g., `:10m`):
```nginx
# Correct format - must include :10m (size)
limit_req_zone $binary_remote_addr zone=api_limit:10m rate=20r/s;
limit_req_zone $binary_remote_addr zone=web_limit:10m rate=30r/s;
limit_req_zone $binary_remote_addr zone=login_limit:10m rate=5r/m;
limit_conn_zone $binary_remote_addr zone=addr:10m;
```

**Check for duplicate zone definitions:**
```bash
# Zones should only be defined once in nginx.conf
sudo grep -rn "zone=addr" /etc/nginx/
```

Remove any duplicate definitions from site configs.

**After fixing:**
```bash
sudo nginx -t
sudo systemctl reload nginx
```

#### High Memory Usage

```bash
# Check what's using memory
sudo ps aux --sort=-%mem | head -n 20

# Clear memory cache (safe)
sudo sync && echo 3 | sudo tee /proc/sys/vm/drop_caches
```

#### Disk Space Issues

```bash
# Find large files
sudo du -ah /var | sort -rh | head -n 20

# Clear old logs
sudo journalctl --vacuum-time=7d

# Clear old backups
sudo find /var/backups/arguspam -name "*.gz" -mtime +7 -delete
```

#### Database Connection Issues

```bash
# Check MySQL is running
sudo systemctl status mysql

# Check MySQL connections
sudo mysql -u root -p -e "SHOW PROCESSLIST;"

# Restart MySQL
sudo systemctl restart mysql
```

---

## Security Best Practices

1. **Keep System Updated**
   ```bash
   sudo apt update && sudo apt upgrade -y
   ```

2. **Regular Backups**
   - Run daily automated backups
   - Test restore procedures monthly

3. **Monitor Logs**
   - Check logs regularly for suspicious activity
   - Setup log monitoring with fail2ban

4. **Use Strong Passwords**
   - Use password manager
   - Rotate passwords quarterly

5. **Firewall Configuration**
   - Only open necessary ports
   - Use UFW or AWS Security Groups

6. **SSL/TLS**
   - Keep certificates up to date
   - Use strong cipher suites

7. **Database Security**
   - Use strong passwords
   - Limit remote access
   - Regular security updates

8. **Application Security**
   - Keep Laravel and dependencies updated
   - Review security advisories
   - Use `composer audit`

---

## Scaling Considerations

When you outgrow t4g.medium (4GB RAM):

### Vertical Scaling (Upgrade Instance)
- **t4g.large** (8GB RAM, 2 vCPU) - $60/month
- **t4g.xlarge** (16GB RAM, 4 vCPU) - $120/month

### Horizontal Scaling (Add Services)
- **Separate Database Server** - Run MySQL on dedicated RDS instance
- **Separate Cache Server** - Run Redis on ElastiCache
- **Load Balancer** - Use AWS ALB for multiple web servers
- **CDN** - Use CloudFront for static assets

### Performance Monitoring
- Setup CloudWatch monitoring
- Use APM tools (New Relic, DataDog)
- Monitor application metrics

---

## Support and Resources

- **Laravel Documentation:** https://laravel.com/docs
- **SvelteKit Documentation:** https://kit.svelte.dev/docs
- **Nginx Documentation:** https://nginx.org/en/docs/
- **MySQL Documentation:** https://dev.mysql.com/doc/
- **Redis Documentation:** https://redis.io/documentation

---

## Checklist

- [ ] Initial server setup completed
- [ ] Swap configured
- [ ] Firewall configured
- [ ] MySQL installed and optimized
- [ ] Redis installed and configured
- [ ] PHP 8.3 installed and optimized
- [ ] Nginx installed and configured
- [ ] Composer installed
- [ ] Node.js installed
- [ ] Application cloned and configured
- [ ] Database migrations run
- [ ] Nginx sites configured
- [ ] Systemd services created and running
- [ ] SSL certificates installed
- [ ] Log rotation configured
- [ ] Cron jobs configured
- [ ] Fail2ban configured
- [ ] Monitoring scripts created
- [ ] Backup scripts configured
- [ ] Deployment scripts created
- [ ] Health checks passing
- [ ] Application accessible via domain

---

**Deployment Information**

**Deployment Date:** _______________

**Deployed By:** _______________

**Server IP:** _______________

**Domains:**
- Web: https://yourdomain.com
- API: https://api.yourdomain.com

---

*Last Updated: November 4, 2025*

