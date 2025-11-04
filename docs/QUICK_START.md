# ArgusPAM Quick Start Guide

Get ArgusPAM up and running in minutes!

## Prerequisites Checklist

Before you start, make sure you have:

- [ ] **Docker** and **Docker Compose** installed ([Install Docker](https://docs.docker.com/get-docker/))
- [ ] A **domain name** (for production) or use localhost for testing
- [ ] **SMTP credentials** (Gmail, SendGrid, Mailgun, AWS SES, etc.)
- [ ] **OpenAI API key** ([Get one here](https://platform.openai.com/api-keys))
- [ ] **15-30 minutes** of your time

## Method 1: Interactive Setup (Recommended for Beginners)

The easiest way to get started:

```bash
# 1. Clone the repository
git clone https://github.com/lunzai/arguspam.git
cd arguspam

# 2. Run the setup script
./setup.sh

# 3. Follow the prompts and answer a few questions

# 4. Start ArgusPAM (command will be shown by setup script)
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# 5. Wait ~30 seconds for containers to be healthy, then run installation:
docker exec -it arguspam-api php artisan pam:install
```

The setup script will:
- ✓ Generate secure database passwords automatically
- ✓ Generate Laravel application key automatically  
- ✓ Ask you for essential configuration (domain, email, OpenAI)
- ✓ Create a ready-to-use `.env` file
- ✓ Show you the next steps

**Time required:** ~10 minutes

## Method 2: Manual Configuration

For those who prefer manual setup:

```bash
# 1. Clone the repository
git clone https://github.com/lunzai/arguspam.git
cd arguspam

# 2. Copy the template
cp env.template .env

# 3. Edit the .env file with your values
nano .env

# 4. Start ArgusPAM (choose your server size)

# For Small server (2 cores, 4GB RAM):
docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file env.prod.small.example up -d

# For Medium server (4 cores, 8GB RAM):
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# For Large server (8+ cores, 16GB RAM):
docker compose -f docker-compose.yml -f docker-compose.prod.yml --env-file env.prod.large.example up -d

# 5. Run installation wizard (after containers are healthy)
docker exec -it arguspam-api php artisan pam:install
```

**Time required:** ~15-20 minutes

## Method 3: Development / Local Testing

Quick local setup for development or testing with hot-reload:

### Option A: Interactive Setup (Recommended)

```bash
# 1. Clone the repository
git clone https://github.com/lunzai/arguspam.git
cd arguspam

# 2. Run interactive setup and choose "Development" mode
chmod +x setup.sh
./setup.sh

# The script will:
# - Prompt for volume mount paths (default: ./api and ./web)
# - Create .env with development settings
# - Show you how to start the services
```

### Option B: Manual Setup

```bash
# 1. Clone the repository
git clone https://github.com/lunzai/arguspam.git
cd arguspam

# 2. Create minimal .env for local testing
cat > .env << EOF
DB_ROOT_PASSWORD=root
DB_PASSWORD=secret
APP_KEY=base64:$(openssl rand -base64 32)
APP_URL=http://localhost:8000
APP_WEB_URL=http://localhost:3000
WEB_ORIGIN=http://localhost:3000
PUBLIC_API_URL=http://localhost:8000
SANCTUM_STATEFUL_DOMAINS=localhost:3000
CORS_ALLOWED_ORIGINS=http://localhost:3000
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_FROM_ADDRESS=noreply@localhost
OPENAI_API_KEY=your_openai_key
OPENAI_ORGANIZATION=
EMAIL_DEFAULT=admin@localhost
EMAIL_SUPPORT=support@localhost

# Optional: Custom mount paths (default: ./api and ./web)
HOST_API_PATH=./api
HOST_WEB_PATH=./web
EOF

# 3. Start in development mode (hot-reload enabled)
docker compose up -d

# 4. Run installation wizard (after containers are healthy)
docker exec -it arguspam-api php artisan pam:install

# Access at:
# - Web: http://localhost:3000
# - API: http://localhost:8000
```

**Time required:** ~5 minutes (testing only, not for production)

**Note:** Replace `your_mailtrap_username`, `your_mailtrap_password`, and `your_openai_key` with actual values, or use `./setup.sh` instead.

## One-Liner Commands

### Production Deployment

```bash
# Clone, setup, and deploy in one go
git clone https://github.com/lunzai/arguspam.git && cd arguspam && ./setup.sh && docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d && sleep 30 && docker exec -it arguspam-api php artisan pam:install
```

### Development Environment

```bash
# Quick development setup
git clone https://github.com/lunzai/arguspam.git && cd arguspam && docker compose up -d && sleep 30 && docker exec -it arguspam-api php artisan pam:install
```

**Note:** Alternatively, download the source code zip from [GitHub](https://github.com/lunzai/arguspam) and extract it.

## Verifying Installation

After starting, verify everything is running:

```bash
# Check service status
docker compose ps

# View logs
docker compose logs -f

# Check individual service
docker compose logs -f api
docker compose logs -f web
```

All services should show as "healthy" or "running".

## Accessing ArgusPAM

### Production (with domain)
- **Web Interface:** `https://yourdomain.com`
- **API:** `https://api.yourdomain.com`

### Local Development
- **Web Interface:** `http://localhost:3000`
- **API:** `http://localhost:8000`

## Common Operations

### Stop ArgusPAM
```bash
docker compose down
```

### Restart ArgusPAM
```bash
docker compose restart
```

### View Logs
```bash
# All services
docker compose logs -f

# Specific service
docker compose logs -f api
docker compose logs -f web
```

### Update ArgusPAM
```bash
git pull
docker compose pull
docker compose up -d
```

### Check Resource Usage
```bash
docker stats
```

## Server Size Guide

Choose the right size for your needs:

| Size | CPU | RAM | Concurrent Users | Team Size | Monthly Cost* |
|------|-----|-----|------------------|-----------|---------------|
| **Small** | 2 cores | 4GB | 50-200 | 5-20 people | $20-40 |
| **Medium** | 4 cores | 8GB | 200-1000 | 20-100 people | $40-80 |
| **Large** | 8 cores | 16GB | 1000-5000 | 100-500 people | $80-160 |

*Estimated costs for DigitalOcean/AWS Lightsail

## What to Do If Something Goes Wrong

### Services won't start
```bash
# Check for errors
docker compose logs

# Try stopping and restarting
docker compose down
docker compose up -d
```

### Port conflicts
```bash
# If ports 3000 or 8000 are in use, modify your .env:
echo "DEV_API_HOST_PORT=8080" >> .env
echo "DEV_WEB_HOST_PORT=3001" >> .env
```

### Can't connect to database
```bash
# Wait for MySQL to be healthy
docker compose ps

# Check MySQL logs
docker compose logs mysql
```

### Out of memory
```bash
# Check resource usage
docker stats

# Scale down or upgrade server
```

## Next Steps

1. **Set up SSL/HTTPS** - See [DEPLOYMENT.md](DEPLOYMENT.md#ssl-https-setup)
2. **Configure backups** - See [DEPLOYMENT.md](DEPLOYMENT.md#backup--restore)
3. **Customize settings** - See [DOCKER_ENV_VARIABLES.md](DOCKER_ENV_VARIABLES.md)
4. **Scale resources** - See [QUICK_REFERENCE.md](QUICK_REFERENCE.md)

## Need Help?

- **Full Documentation:** [DEPLOYMENT.md](DEPLOYMENT.md)
- **Environment Variables:** [DOCKER_ENV_VARIABLES.md](DOCKER_ENV_VARIABLES.md)
- **Command Reference:** [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
- **Troubleshooting:** [DEPLOYMENT.md#troubleshooting](DEPLOYMENT.md#troubleshooting)

## Success Criteria

You're all set when:
- ✓ All Docker containers are running
- ✓ You can access the web interface
- ✓ You can log in with your admin credentials
- ✓ Email notifications are working
- ✓ SSL/HTTPS is configured (for production)

**Congratulations! ArgusPAM is ready to use.** 🎉

