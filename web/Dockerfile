# Build arguments (can be overridden in docker-compose.yml)
ARG NODE_VERSION=20-alpine
ARG WORKDIR=/app
ARG WEB_PORT=3000
ARG VITE_HMR_PORT=5173
ARG NODEJS_GROUP_ID=1001
ARG NODEJS_USER_ID=1001

# Base stage with Node.js LTS
FROM node:${NODE_VERSION} AS base

# Install dependencies only when needed
FROM base AS deps

# Redeclare ARGs for this stage
ARG WORKDIR=/app

# Check https://github.com/nodejs/docker-node/tree/b4117f9333da4138b03a546ec926ef50a31506c3#nodealpine to understand why libc6-compat might be needed.
RUN apk add --no-cache libc6-compat

WORKDIR ${WORKDIR}

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm ci

# Development stage
FROM base AS development

# Redeclare ARGs for this stage
ARG WORKDIR=/app
ARG WEB_PORT=3000
ARG VITE_HMR_PORT=5173

WORKDIR ${WORKDIR}

# Copy dependencies from deps stage
COPY --from=deps ${WORKDIR}/node_modules ./node_modules
COPY . .

# Expose ports (app port and Vite HMR port)
EXPOSE ${WEB_PORT} ${VITE_HMR_PORT}

# Start development server
CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]

# Builder stage
FROM base AS builder

# Redeclare ARGs for this stage
ARG WORKDIR=/app
# Build-time environment variables for SvelteKit (PUBLIC_)
ARG PUBLIC_API_URL
ARG PUBLIC_API_REQUEST_TIMEOUT=60000
ARG PUBLIC_AUTH_LOGIN_PATH=/auth/login
ARG PUBLIC_AUTH_LOGOUT_PATH=/auth/logout
ARG PUBLIC_ORG_ID_HEADER=X-Organization-ID
ARG PUBLIC_ACCESS_REQUEST_MIN_DURATION=10
ARG PUBLIC_ACCESS_REQUEST_MAX_DURATION=43200
# Build-time environment variables for SvelteKit (private)
ARG COOKIE_EXPIRY=86400
ARG COOKIE_SAME_SITE=lax
ARG COOKIE_TOKEN_KEY=auth_token
ARG COOKIE_CURRENT_ORG_KEY=current_org_id
ARG COOKIE_TEMP_KEY_KEY=temp_key

WORKDIR ${WORKDIR}

# Copy dependencies
COPY --from=deps ${WORKDIR}/node_modules ./node_modules

# Copy source code
COPY . .

# Build application
# Disable telemetry during build
# Set PUBLIC_ environment variables for SvelteKit build
ENV NEXT_TELEMETRY_DISABLED=1
ENV NODE_ENV=production
ENV PUBLIC_API_URL=${PUBLIC_API_URL}
ENV PUBLIC_API_REQUEST_TIMEOUT=${PUBLIC_API_REQUEST_TIMEOUT}
ENV PUBLIC_AUTH_LOGIN_PATH=${PUBLIC_AUTH_LOGIN_PATH}
ENV PUBLIC_AUTH_LOGOUT_PATH=${PUBLIC_AUTH_LOGOUT_PATH}
ENV PUBLIC_ORG_ID_HEADER=${PUBLIC_ORG_ID_HEADER}
ENV PUBLIC_ACCESS_REQUEST_MIN_DURATION=${PUBLIC_ACCESS_REQUEST_MIN_DURATION}
ENV PUBLIC_ACCESS_REQUEST_MAX_DURATION=${PUBLIC_ACCESS_REQUEST_MAX_DURATION}
ENV COOKIE_EXPIRY=${COOKIE_EXPIRY}
ENV COOKIE_SAME_SITE=${COOKIE_SAME_SITE}
ENV COOKIE_TOKEN_KEY=${COOKIE_TOKEN_KEY}
ENV COOKIE_CURRENT_ORG_KEY=${COOKIE_CURRENT_ORG_KEY}
ENV COOKIE_TEMP_KEY_KEY=${COOKIE_TEMP_KEY_KEY}

RUN npm run build

# Production stage
FROM base AS production

# Redeclare ARGs for this stage
ARG WORKDIR=/app
ARG WEB_PORT=3000
ARG NODEJS_GROUP_ID=1001
ARG NODEJS_USER_ID=1001

WORKDIR ${WORKDIR}

ENV NODE_ENV=production
ENV PORT=${WEB_PORT}

# Create non-root user
RUN addgroup --system --gid ${NODEJS_GROUP_ID} nodejs && \
    adduser --system --uid ${NODEJS_USER_ID} sveltekit

# Copy built application
COPY --from=builder --chown=sveltekit:nodejs ${WORKDIR}/build ./build
COPY --from=builder --chown=sveltekit:nodejs ${WORKDIR}/package*.json ./

# Install production dependencies only
RUN npm ci --only=production && npm cache clean --force

# Switch to non-root user
USER sveltekit

# Expose port
EXPOSE ${WEB_PORT}

# Health check is configured in docker-compose.yml for flexibility
# Default: node -e "require('http').get('http://localhost:3000', ...)"

# Start the application
CMD ["node", "build/index.js"]
