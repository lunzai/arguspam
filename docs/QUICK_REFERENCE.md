# Docker Compose Dynamic Configuration - Quick Reference

## 🚀 Quick Start

### Development
```bash
docker compose up -d
```

### Production - Default Configuration
```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Production - Custom Configuration
```bash
cp env.prod.medium.example .env.prod
# Edit .env.prod with your values
docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file .env.prod up -d
```

## 📋 Common Resource Configurations

### Small Server (2-4 cores, 4-8GB RAM)
```bash
# MySQL
MYSQL_CPU_LIMIT=1
MYSQL_MEMORY_LIMIT=1G

# Redis
REDIS_CPU_LIMIT=0.5
REDIS_MEMORY_LIMIT=256M
REDIS_MAXMEMORY=256mb

# API
API_CPU_LIMIT=1
API_MEMORY_LIMIT=512M
```

### Medium Server (4-8 cores, 8-16GB RAM) - DEFAULT
```bash
# MySQL
MYSQL_CPU_LIMIT=2
MYSQL_MEMORY_LIMIT=2G

# Redis
REDIS_CPU_LIMIT=1
REDIS_MEMORY_LIMIT=512M
REDIS_MAXMEMORY=512mb

# API
API_CPU_LIMIT=2
API_MEMORY_LIMIT=1G
```

### Large Server (8+ cores, 16GB+ RAM)
```bash
# MySQL
MYSQL_CPU_LIMIT=4
MYSQL_MEMORY_LIMIT=4G

# Redis
REDIS_CPU_LIMIT=2
REDIS_MEMORY_LIMIT=2G
REDIS_MAXMEMORY=2gb

# API
API_CPU_LIMIT=4
API_MEMORY_LIMIT=2G
```

## 🔧 Common Operations

### Scale MySQL Resources
```bash
MYSQL_CPU_LIMIT=4 MYSQL_MEMORY_LIMIT=4G \
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d mysql
```

### Scale Redis Resources
```bash
REDIS_CPU_LIMIT=2 REDIS_MEMORY_LIMIT=1G REDIS_MAXMEMORY=1gb \
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d redis
```

### Change Development Ports
```bash
# API on port 8080 instead of 8000
DEV_API_HOST_PORT=8080 docker compose up -d

# Web on port 3001 instead of 3000
DEV_WEB_HOST_PORT=3001 docker compose up -d
```

### Adjust Logging
```bash
# Increase API log size
API_LOG_MAX_SIZE=100m API_LOG_MAX_FILE=10 \
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d api
```

## 📊 Resource Allocation Cheat Sheet

| Service | Default CPU | Default Memory | Small | Medium | Large |
|---------|-------------|----------------|-------|--------|-------|
| MySQL   | 2 cores     | 2G            | 1C/1G | 2C/2G  | 4C/4G |
| Redis   | 1 core      | 512M          | 0.5C/256M | 1C/512M | 2C/2G |
| API     | 2 cores     | 1G            | 1C/512M | 2C/1G | 4C/2G |
| Horizon | 1 core      | 512M          | 0.5C/256M | 1C/512M | 2C/1G |
| Web     | 1 core      | 512M          | 0.5C/256M | 1C/512M | 2C/1G |
| **Total Limits** | **7 cores** | **4.5G** | **3.5C/2.25G** | **7C/4.5G** | **14C/10G** |

## 🔑 Most Common Variables

### Production Resource Limits
```bash
# MySQL
MYSQL_CPU_LIMIT=2
MYSQL_MEMORY_LIMIT=2G
MYSQL_CPU_RESERVATION=0.5
MYSQL_MEMORY_RESERVATION=512M

# Redis
REDIS_CPU_LIMIT=1
REDIS_MEMORY_LIMIT=512M
REDIS_CPU_RESERVATION=0.25
REDIS_MEMORY_RESERVATION=128M
REDIS_MAXMEMORY=512mb

# API
API_CPU_LIMIT=2
API_MEMORY_LIMIT=1G
API_CPU_RESERVATION=0.5
API_MEMORY_RESERVATION=256M

# Horizon
HORIZON_CPU_LIMIT=1
HORIZON_MEMORY_LIMIT=512M

# Web
WEB_CPU_LIMIT=1
WEB_MEMORY_LIMIT=512M
```

### Development Ports
```bash
MYSQL_HOST_PORT=3306
REDIS_HOST_PORT=6379
DEV_API_HOST_PORT=8000
DEV_WEB_HOST_PORT=3000
DEV_VITE_HMR_PORT=5173
```

### Development Credentials
```bash
DEV_DB_ROOT_PASSWORD=root
DEV_DB_DATABASE=arguspam
DEV_DB_USERNAME=arguspam
DEV_DB_PASSWORD=secret
```

### Logging Configuration
```bash
# MySQL
MYSQL_LOG_MAX_SIZE=10m
MYSQL_LOG_MAX_FILE=3

# API
API_LOG_MAX_SIZE=50m
API_LOG_MAX_FILE=5
```

## 🛠️ Useful Commands

### Validate Configuration
```bash
# Base
docker compose -f docker-compose.yml config --quiet

# Production
docker compose -f docker-compose.yml -f docker-compose.prod.yml config --quiet

# With custom env file
docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file .env.prod config --quiet
```

### View Merged Configuration
```bash
# See what will be deployed (with variable substitution)
docker compose -f docker-compose.yml -f docker-compose.prod.yml config
```

### Monitor Resources
```bash
# Real-time resource usage
docker stats

# One-time snapshot
docker stats --no-stream
```

### Check Service Health
```bash
docker compose ps
docker compose logs -f <service-name>
```

### Restart Single Service
```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml restart <service-name>
```

### Update Single Service with New Resources
```bash
# Scale up MySQL
MYSQL_CPU_LIMIT=4 MYSQL_MEMORY_LIMIT=4G \
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d mysql
```

## 📁 File Reference

| File | Purpose |
|------|---------|
| `env.docker.example` | Complete variable reference with all options |
| `env.prod.small.example` | Small server configuration template |
| `env.prod.medium.example` | Medium server configuration template |
| `env.prod.large.example` | Large server configuration template |
| `DOCKER_ENV_VARIABLES.md` | Comprehensive documentation |
| `CHANGES_SUMMARY.md` | Overview of all changes |
| `QUICK_REFERENCE.md` | This file - quick commands and configs |

## 🎯 Common Scenarios

### Scenario 1: First Time Deployment (Production)
```bash
# 1. Choose server size and copy template
cp env.prod.medium.example .env.prod

# 2. Edit with your actual values
nano .env.prod

# 3. Validate configuration
docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file .env.prod config --quiet

# 4. Deploy
docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file .env.prod up -d

# 5. Monitor
docker stats
```

### Scenario 2: Scale Up During High Traffic
```bash
# Quick scale (temporary)
MYSQL_CPU_LIMIT=4 MYSQL_MEMORY_LIMIT=4G \
REDIS_CPU_LIMIT=2 REDIS_MEMORY_LIMIT=1G REDIS_MAXMEMORY=1gb \
API_CPU_LIMIT=4 API_MEMORY_LIMIT=2G \
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Or update .env.prod for permanent change
```

### Scenario 3: Staging Environment
```bash
# Create staging config (between small and medium)
cp env.prod.small.example .env.staging

# Customize
nano .env.staging

# Deploy
docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file .env.staging up -d
```

### Scenario 4: Development with Custom Ports
```bash
# Create local dev config
echo "DEV_API_HOST_PORT=8080" > .env
echo "DEV_WEB_HOST_PORT=3001" >> .env
echo "MYSQL_HOST_PORT=3307" >> .env

# Start
docker compose up -d
```

### Scenario 5: Debugging with Increased Logging
```bash
# Temporarily increase log retention
API_LOG_MAX_SIZE=200m API_LOG_MAX_FILE=20 \
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d api
```

## ⚠️ Important Notes

1. **REDIS_MAXMEMORY should be < REDIS_MEMORY_LIMIT**
   ```bash
   REDIS_MEMORY_LIMIT=512M  # Docker limit
   REDIS_MAXMEMORY=512mb    # Redis internal limit (slightly less is safer)
   ```

2. **Always validate before deploying**
   ```bash
   docker compose -f docker-compose.yml -f docker-compose.prod.yml config --quiet
   ```

3. **Monitor after scaling**
   ```bash
   docker stats
   docker compose logs -f
   ```

4. **Keep .env files secure**
   - Never commit `.env` files to git
   - Only commit `.env.example` files
   - Use different credentials per environment

5. **Test configuration changes in staging first**

## 🔍 Troubleshooting

### Container keeps restarting
```bash
# Check logs
docker compose logs <service-name>

# Check if resource limits are too restrictive
docker stats

# Increase limits if needed
```

### Out of memory
```bash
# Check current usage
docker stats --no-stream

# Identify the service using most memory
# Increase its memory limit
<SERVICE>_MEMORY_LIMIT=2G docker compose ... up -d <service>
```

### Port conflicts
```bash
# Change the host port (development)
DEV_API_HOST_PORT=8080 docker compose up -d
```

### Performance issues
```bash
# Monitor resources
docker stats

# Check if hitting limits
docker inspect <container-name> | grep -A 20 Resources

# Scale up if needed
```

## 📚 Further Reading

- Full documentation: `DOCKER_ENV_VARIABLES.md`
- All available variables: `env.docker.example`
- Change history: `CHANGES_SUMMARY.md`

