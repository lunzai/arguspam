# Docker Compose Environment Variables Guide

## Overview

All hardcoded values in the Docker Compose files have been made dynamic using environment variables with sensible defaults. This allows you to customize your deployment without modifying the compose files directly.

## Quick Start

### Development

1. Copy the example environment file:
```bash
cp env.docker.example .env
```

2. Start the development environment (uses `docker-compose.override.yml` automatically):
```bash
docker compose up -d
```

### Production

1. Choose a configuration based on your server size:
   - **Small Server** (2-4 cores, 4-8GB RAM): `env.prod.small.example`
   - **Medium Server** (4-8 cores, 8-16GB RAM): `env.prod.medium.example`
   - **Large Server** (8+ cores, 16GB+ RAM): `env.prod.large.example`

2. Copy and customize:
```bash
# Example for medium server
cp env.prod.medium.example .env.prod
# Edit .env.prod with your actual values
```

3. Deploy:
```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file .env.prod up -d
```

## Environment Variable Categories

### 1. Container Configuration

Control Docker image versions and container names:

```bash
# Container Images
MYSQL_IMAGE=mysql:8.0
REDIS_IMAGE=redis:7-alpine

# Container Names
MYSQL_CONTAINER_NAME=arguspam-mysql
REDIS_CONTAINER_NAME=arguspam-redis
API_CONTAINER_NAME=arguspam-api
HORIZON_CONTAINER_NAME=arguspam-horizon
WEB_CONTAINER_NAME=arguspam-web
```

### 2. Build Targets

Switch between development and production builds:

```bash
API_BUILD_TARGET=production        # or 'development'
HORIZON_BUILD_TARGET=production
WEB_BUILD_TARGET=production
```

### 3. Resource Limits (Production)

#### MySQL Resources
```bash
MYSQL_CPU_LIMIT=2                  # Max CPUs
MYSQL_MEMORY_LIMIT=2G              # Max memory
MYSQL_CPU_RESERVATION=0.5          # Guaranteed CPUs
MYSQL_MEMORY_RESERVATION=512M      # Guaranteed memory
```

#### Redis Resources
```bash
REDIS_CPU_LIMIT=1
REDIS_MEMORY_LIMIT=512M
REDIS_CPU_RESERVATION=0.25
REDIS_MEMORY_RESERVATION=128M
REDIS_MAXMEMORY=512mb              # Redis internal limit
REDIS_MAXMEMORY_POLICY=allkeys-lru # Eviction policy
```

#### API Resources
```bash
API_CPU_LIMIT=2
API_MEMORY_LIMIT=1G
API_CPU_RESERVATION=0.5
API_MEMORY_RESERVATION=256M
```

#### Horizon Resources
```bash
HORIZON_CPU_LIMIT=1
HORIZON_MEMORY_LIMIT=512M
HORIZON_CPU_RESERVATION=0.25
HORIZON_MEMORY_RESERVATION=128M
```

#### Web Resources
```bash
WEB_CPU_LIMIT=1
WEB_MEMORY_LIMIT=512M
WEB_CPU_RESERVATION=0.25
WEB_MEMORY_RESERVATION=128M
```

### 4. Logging Configuration

Configure log rotation for each service:

```bash
# MySQL Logging
MYSQL_LOG_DRIVER=json-file
MYSQL_LOG_MAX_SIZE=10m
MYSQL_LOG_MAX_FILE=3

# Redis Logging
REDIS_LOG_DRIVER=json-file
REDIS_LOG_MAX_SIZE=10m
REDIS_LOG_MAX_FILE=3

# API Logging
API_LOG_DRIVER=json-file
API_LOG_MAX_SIZE=50m
API_LOG_MAX_FILE=5

# Similar for HORIZON and WEB
```

### 5. Health Check Configuration

Customize health check timing:

```bash
MYSQL_HEALTHCHECK_INTERVAL=10s
MYSQL_HEALTHCHECK_TIMEOUT=5s
MYSQL_HEALTHCHECK_RETRIES=5
MYSQL_HEALTHCHECK_START_PERIOD=30s
```

### 6. Port Mappings (Development)

Control which ports are exposed on your host:

```bash
MYSQL_HOST_PORT=3306
REDIS_HOST_PORT=6379
DEV_API_HOST_PORT=8000
DEV_WEB_HOST_PORT=3000
DEV_VITE_HMR_PORT=5173
```

### 7. Restart Policies

Control container restart behavior:

```bash
MYSQL_RESTART_POLICY=always
REDIS_RESTART_POLICY=always
API_RESTART_POLICY=always
HORIZON_RESTART_POLICY=always
WEB_RESTART_POLICY=always
```

## Usage Examples

### Example 1: Scale Up for High Traffic

Create a custom `.env.hightraffic`:

```bash
# Use large server defaults as base
cat env.prod.large.example > .env.hightraffic

# Further customize if needed
echo "MYSQL_CPU_LIMIT=6" >> .env.hightraffic
echo "MYSQL_MEMORY_LIMIT=6G" >> .env.hightraffic
```

Deploy:
```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file .env.hightraffic up -d
```

### Example 2: Resource-Constrained Server

```bash
# Copy small server config
cp env.prod.small.example .env.prod

# Adjust further if needed
# Edit .env.prod and reduce values
```

### Example 3: Different Configurations per Environment

```bash
# Staging environment
docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file .env.staging up -d

# Production environment
docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file .env.production up -d
```

### Example 4: Override Single Values

Use environment variables directly:

```bash
# Scale up MySQL on the fly
MYSQL_CPU_LIMIT=4 MYSQL_MEMORY_LIMIT=4G \
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d mysql
```

## Resource Allocation Guidelines

### Small Server (2-4 cores, 4-8GB RAM)
- **Total Reserved**: ~0.7 CPUs, ~576M RAM
- **Total Limits**: ~3.5 CPUs, ~2.25G RAM
- **Suitable for**: Development, staging, low-traffic apps

### Medium Server (4-8 cores, 8-16GB RAM) - DEFAULT
- **Total Reserved**: ~1.75 CPUs, ~1.5G RAM
- **Total Limits**: ~7 CPUs, ~4.5G RAM
- **Suitable for**: Production, moderate traffic

### Large Server (8+ cores, 16GB+ RAM)
- **Total Reserved**: ~3.5 CPUs, ~3G RAM
- **Total Limits**: ~14 CPUs, ~10G RAM
- **Suitable for**: High traffic, enterprise deployments

## Best Practices

1. **Always use defaults as starting point**: The defaults in `docker-compose.prod.yml` are tested and balanced.

2. **Monitor before scaling**: Use `docker stats` to see actual resource usage before increasing limits.

3. **Reserve resources conservatively**: Set reservations to what you know the service needs at minimum.

4. **Set limits generously**: Limits should allow for traffic spikes but prevent runaway processes.

5. **Keep Redis maxmemory in sync**: Ensure `REDIS_MAXMEMORY` is slightly less than `REDIS_MEMORY_LIMIT`.

6. **Environment-specific files**: Use different `.env` files for dev, staging, and production.

7. **Don't commit secrets**: Add `.env*` to `.gitignore` (except `.example` files).

## Troubleshooting

### Container keeps restarting

Check if resource limits are too restrictive:
```bash
docker compose logs <service-name>
docker stats
```

### Out of memory errors

Increase memory limits:
```bash
MYSQL_MEMORY_LIMIT=4G docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d mysql
```

### Performance issues

Monitor actual usage and adjust:
```bash
docker stats --no-stream
```

## Migration from Hardcoded Values

If you were using the old hardcoded values, the defaults in the compose files match the original values. No changes needed unless you want to customize.

## Need Help?

- Check `env.docker.example` for all available variables
- Review server size examples: `env.prod.{small,medium,large}.example`
- Monitor resources: `docker stats`
- View logs: `docker compose logs -f <service-name>`

