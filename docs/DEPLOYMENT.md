# ArgusPAM Deployment Guide

Complete guide for deploying ArgusPAM from scratch to production.

---

## Table of Contents

### Quick Setup (For Everyone)
- [Before You Begin](#before-you-begin)
- [Step 1: Get a Server](#step-1-get-a-server)
- [Step 2: Point Your Domain](#step-2-point-your-domain)
- [Step 3: Install Dependencies](#step-3-install-dependencies)
- [Step 4: Run Setup](#step-4-run-setup)
- [Step 5: Start the Application](#step-5-start-the-application)
- [Quick Maintenance](#quick-maintenance)

### Advanced Configuration (For Technical Teams)
- [Manual Configuration](#manual-configuration)
- [SSL/HTTPS Setup](#ssl-https-setup)
- [Production Hardening](#production-hardening)
- [Scaling & Performance](#scaling--performance)
- [Troubleshooting](#troubleshooting)

---

# Quick Setup (For Everyone)

This section will guide you through deploying ArgusPAM with minimal technical knowledge. **Expected time: 15-30 minutes.**

## Before You Begin

### What You'll Need

- [ ] A **server** (we'll help you choose one below)
- [ ] A **domain name** (e.g., arguspam.com) - $10-15/year
- [ ] **Email account** (Gmail, Outlook, or any SMTP provider) - Free or existing
- [ ] **OpenAI API key** ([Get one free here](https://platform.openai.com/api-keys))
- [ ] **15-30 minutes** of your time
- [ ] Basic command line knowledge (copy/paste commands)

### Expected Costs

| Item | Cost (Monthly) | Notes |
|------|----------------|-------|
| **Small Server** | $20-40 | For teams of 5-20 people |
| **Medium Server** | $40-80 | For teams of 20-100 people (Recommended) |
| **Large Server** | $80-160 | For organizations of 100-500 people |
| **Domain Name** | ~$1/month | One-time yearly payment |
| **OpenAI API** | $5-50/month | Pay per use, varies by activity |
| **Total (Medium)** | **$46-131/month** | Depends on usage |

---

## Step 1: Get a Server

You need a server (virtual machine) running Linux to host ArgusPAM. Here's how to get one:

### Option A: DigitalOcean (Recommended for Beginners)

1. **Sign up** at [digitalocean.com](https://www.digitalocean.com)
2. **Create a Droplet:**
   - Click "Create" → "Droplets"
   - **Choose Image:** Ubuntu 22.04 LTS
   - **Choose Size:**
     - **Small:** Basic - $24/month (2GB RAM, 2 CPU) - For 5-20 people
     - **Medium:** Basic - $48/month (4GB RAM, 2 CPU) - For 20-100 people ⭐ Recommended
     - **Large:** Basic - $96/month (8GB RAM, 4 CPU) - For 100-500 people
   - **Choose Region:** Pick closest to your team
   - **Authentication:** Password or SSH key (we'll use password for simplicity)
   - Click **"Create Droplet"**

3. **Note your server's IP address** (e.g., `123.45.67.89`)

### Option B: AWS Lightsail

1. **Sign up** at [lightsail.aws.amazon.com](https://lightsail.aws.amazon.com)
2. **Create an instance:**
   - **Platform:** Linux/Unix
   - **Blueprint:** OS Only → Ubuntu 22.04 LTS
   - **Instance Plan:**
     - **Small:** $20/month (2GB RAM, 1 CPU) - For 5-20 people
     - **Medium:** $40/month (4GB RAM, 2 CPU) - For 20-100 people ⭐ Recommended
     - **Large:** $80/month (8GB RAM, 2 CPU) - For 100-500 people
   - **Name your instance:** arguspam
   - Click **"Create instance"**

3. **Note your server's public IP address**

**Why Lightsail?** Fixed pricing ($20-80/month all-inclusive), simple setup, perfect for ArgusPAM's needs.

### Option C: AWS EC2 (For Advanced Users)

**When to choose EC2:**
- You need auto-scaling for traffic spikes
- You require custom VPC networking or advanced AWS features
- You're already familiar with AWS infrastructure
- You need integration with other AWS services (RDS, ElastiCache, etc.)

**Cost:** Variable - typically $30-120/month (instance + storage + data transfer)

1. **Sign up** at [aws.amazon.com](https://aws.amazon.com)

2. **Launch an EC2 instance:**
   - Go to **EC2 Dashboard** → Click **"Launch Instance"**
   
   - **Name:** arguspam-server
   
   - **Application and OS Images (AMI):**
     - Quick Start: **Ubuntu**
     - Ubuntu Server 22.04 LTS (Free tier eligible)
   
   - **Instance Type:**
     - **Small:** t3.small (2 vCPU, 2GB RAM) - ~$15/month
     - **Medium:** t3.medium (2 vCPU, 4GB RAM) - ~$30/month ⭐ Recommended
     - **Large:** t3.large (2 vCPU, 8GB RAM) - ~$60/month
     - **Extra Large:** t3.xlarge (4 vCPU, 16GB RAM) - ~$120/month
   
   - **Key Pair (login):**
     - Create new key pair → Name: `arguspam-key`
     - Download the `.pem` file and **keep it safe**
   
   - **Network Settings:**
     - Click **"Edit"**
     - **Allow SSH traffic from:** Your IP (for security)
     - **Allow HTTPS traffic from the internet:** ✓
     - **Allow HTTP traffic from the internet:** ✓
     - Click **"Add security group rule"**:
       - Type: Custom TCP
       - Port: 3000
       - Source: 0.0.0.0/0 (Anywhere)
     - Click **"Add security group rule"** again:
       - Type: Custom TCP
       - Port: 8000
       - Source: 0.0.0.0/0 (Anywhere)
   
   - **Configure Storage:**
     - **Size:** 30 GiB (minimum)
     - **Volume Type:** gp3 (recommended for better performance)
   
   - Click **"Launch Instance"**

3. **Note your instance's Public IPv4 address**

4. **Connect to your instance:**
   ```bash
   # On Mac/Linux:
   chmod 400 arguspam-key.pem
   ssh -i arguspam-key.pem ubuntu@YOUR_EC2_PUBLIC_IP
   
   # On Windows (use PowerShell):
   ssh -i arguspam-key.pem ubuntu@YOUR_EC2_PUBLIC_IP
   ```

**EC2 vs Lightsail Comparison:**

| Feature | Lightsail | EC2 |
|---------|-----------|-----|
| **Ease of Setup** | ⭐⭐⭐⭐⭐ Simple | ⭐⭐ Complex |
| **Setup Time** | 5 minutes | 15-20 minutes |
| **Pricing Model** | Fixed monthly | Variable (instance + storage + data transfer) |
| **Data Transfer** | 1-3TB included | $0.09/GB (can add up quickly) |
| **Best For** | Predictable workloads | Auto-scaling, advanced features |
| **Technical Knowledge** | Beginner-friendly | AWS expertise helpful |
| **ArgusPAM Recommendation** | ✅ Perfect for most users | ⚠️ Only if you need advanced features |

**Note:** For most ArgusPAM deployments, **Lightsail or DigitalOcean** are simpler and more cost-effective. Choose EC2 only if you specifically need its advanced capabilities.

### Server Sizing Guide

Choose based on your team size:

| Size | CPU | RAM | Concurrent Users | Team Size | Use Case |
|------|-----|-----|------------------|-----------|----------|
| Small | 2 cores | 4GB | 50-200 | 5-20 people | Small teams, testing |
| Medium | 4 cores | 8GB | 200-1000 | 20-100 people | Growing companies, SMEs |
| Large | 8 cores | 16GB | 1000-5000 | 100-500 people | Large organizations |

**Not sure?** Start with **Medium** - you can always upgrade later.

---

## Step 2: Point Your Domain

You need a domain name so people can access ArgusPAM (e.g., `arguspam.com`).

### A. Get a Domain Name

If you don't have one yet:
1. Go to **Namecheap**, **GoDaddy**, or **Google Domains**
2. Search for and purchase a domain (e.g., `arguspam.com`)
3. Cost: ~$10-15 per year

### B. Set Up DNS with Cloudflare (Recommended)

Cloudflare provides free DNS, SSL, and DDoS protection:

1. **Sign up** at [cloudflare.com](https://cloudflare.com) (free plan is fine)
2. **Add your domain** (e.g., `arguspam.com`)
3. Cloudflare will give you **nameservers** (e.g., `bella.ns.cloudflare.com`)
4. **Update nameservers** at your domain registrar:
   - Go to your registrar (Namecheap, GoDaddy, etc.)
   - Find "Nameservers" or "DNS Settings"
   - Change to Cloudflare's nameservers
   - Wait 5-30 minutes for changes to apply

5. **Add DNS Records** in Cloudflare:

   Navigate to **DNS** → **Records** and add two **A records**:

   | Type | Name | Content (IP Address) | Proxy Status |
   |------|------|----------------------|--------------|
   | A | @ | Your server IP | Proxied (orange cloud) |
   | A | api | Your server IP | Proxied (orange cloud) |

   **Example:**
   - `arguspam.com` → `123.45.67.89`
   - `api.arguspam.com` → `123.45.67.89`

6. **Wait 5-10 minutes** for DNS to propagate

**Verification:** Try `ping arguspam.com` and `ping api.arguspam.com` - they should respond with your server's IP.

---

## Step 3: Install Dependencies

Now let's connect to your server and install Docker.

### Connect to Your Server

**Using Terminal (Mac/Linux):**
```bash
ssh root@YOUR_SERVER_IP
```

**Using PowerShell (Windows):**
```powershell
ssh root@YOUR_SERVER_IP
```

**Using PuTTY (Windows alternative):**
- Download [PuTTY](https://putty.org)
- Enter your server IP
- Click "Open"

Enter your password when prompted.

### Install Docker

Once connected to your server, run these commands:

```bash
# Update system packages
apt update && apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Install Docker Compose (included in modern Docker)
docker compose version

# Install Git
apt install -y git

# Verify installations
docker --version
docker compose version
git --version
```

All commands should show version numbers (e.g., Docker 24.0.5).

---

## Step 4: Run Setup

Now we'll download ArgusPAM and run the interactive setup script.

```bash
# Clone the repository
git clone https://github.com/lunzai/arguspam.git
cd arguspam

# Run the interactive setup script
./setup.sh
```

**Alternative:** Download the source code directly from [GitHub](https://github.com/lunzai/arguspam):
1. Go to https://github.com/lunzai/arguspam
2. Click "Code" → "Download ZIP"
3. Extract the zip file
4. Open terminal in the extracted folder
5. Run `./setup.sh`

The setup script will ask you a few questions:

### 1. **Select Deployment Size**
```
Choose: 1 (Small), 2 (Medium), or 3 (Large)
```
**Tip:** Choose based on your team size (see table in Step 1)

### 2. **Enter Your Domain**
```
Enter your domain name: arguspam.com
```
Use the domain you set up in Step 2.

### 3. **Email Configuration (SMTP)**

The script will ask for:
- SMTP Host (e.g., `smtp.gmail.com`)
- SMTP Port (usually `587`)
- SMTP Username (your email address)
- SMTP Password

**Gmail Example:**
- Host: `smtp.gmail.com`
- Port: `587`
- Username: `your-email@gmail.com`
- Password: [App Password](https://support.google.com/accounts/answer/185833) (not your regular password)

**Other Providers:**
- **SendGrid:** smtp.sendgrid.net, port 587
- **Mailgun:** smtp.mailgun.org, port 587  
- **AWS SES:** email-smtp.region.amazonaws.com, port 587

### 4. **OpenAI API Key**

Get your API key from [platform.openai.com/api-keys](https://platform.openai.com/api-keys):
1. Sign up (free trial available)
2. Create new API key
3. Copy and paste when prompted

### 5. **Administrator Email**

Enter the email address for the main administrator.

### 6. **Save Your Credentials**

The script will generate and display secure passwords. **Save these somewhere safe!**

```
╔════════════════════════════════════════╗
║  SAVE THESE CREDENTIALS SECURELY       ║
╠════════════════════════════════════════╣
║  MySQL Root Password: [generated]      ║
║  MySQL User Password: [generated]      ║
║  Laravel APP_KEY: [generated]          ║
╚════════════════════════════════════════╝
```

---

## Step 5: Start the Application

After setup completes, start ArgusPAM:

```bash
# Start all services
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

**What this does:**
- Downloads Docker images (first time only, ~5-10 minutes)
- Starts MySQL, Redis, API, and Web services
- Sets up the database
- Makes everything ready to use

### Verify It's Running

```bash
# Check all services are running
docker compose ps
```

You should see all services as "healthy" or "running":
```
NAME                STATUS
arguspam-mysql      Up (healthy)
arguspam-redis      Up (healthy)
arguspam-api        Up (healthy)
arguspam-horizon    Up
arguspam-web        Up (healthy)
```

### View Logs

```bash
# Watch all logs
docker compose logs -f

# Press Ctrl+C to exit

# View specific service
docker compose logs -f api
docker compose logs -f web
```

---

## Access Your Application

### Initial Access (Direct IP)

Before setting up SSL, you can access via your server's IP:

- **Web Interface:** `http://YOUR_SERVER_IP:3000`
- **API:** `http://YOUR_SERVER_IP:8000`

Replace `YOUR_SERVER_IP` with your actual server IP address.

### With Domain (Requires Port Forwarding or Reverse Proxy)

To access via your domain name without port numbers:

**Option 1: Quick Test (with ports)**
- **Web Interface:** `http://arguspam.com:3000`
- **API:** `http://api.arguspam.com:8000`

**Option 2: Production Setup (recommended)**

Set up SSL/HTTPS first (see [SSL/HTTPS Setup](#ssl-https-setup) below), then access:
- **Web Interface:** `https://arguspam.com`
- **API:** `https://api.arguspam.com`

**Note:** If using Cloudflare with proxy enabled (orange cloud), the ports won't work. You'll need to set up SSL first.

### First Login & Setup

After all containers are healthy (check with `docker compose ps`), run the installation wizard:

```bash
docker exec -it arguspam-api php artisan pam:install
```

**The installation wizard will:**
1. Create your first organization
2. Create your admin user account
3. Set up initial roles and permissions
4. Configure basic settings

**Then access your application:**
1. Navigate to your web interface (use IP address initially: `http://YOUR_SERVER_IP:3000`)
2. **Log in** with the credentials you created during installation
3. Start using ArgusPAM!

**Tip:** Check the logs if you have issues accessing:
```bash
docker compose logs -f web
docker compose logs -f api
```

---

## Quick Maintenance

### Stop ArgusPAM

```bash
cd arguspam
docker compose down
```

### Start ArgusPAM

```bash
cd arguspam
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Restart ArgusPAM

```bash
cd arguspam
docker compose restart
```

### View Logs

```bash
cd arguspam
docker compose logs -f
```

### Update ArgusPAM

```bash
cd arguspam
git pull
docker compose pull
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Backup Database

```bash
# Create backup
docker compose exec mysql mysqldump -u arguspam -p arguspam > backup-$(date +%Y%m%d).sql

# Enter the MySQL password when prompted (from setup)
```

### Common Issues

**Services won't start:**
```bash
docker compose logs
docker compose down
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

**Can't access website:**
- Check DNS: `ping arguspam.com`
- Check firewall: Ports 80, 443, 3000, 8000 should be open
- Check logs: `docker compose logs web`

**Out of disk space:**
```bash
# Clean up old Docker images
docker system prune -a
```

**Need help?** See [Troubleshooting](#troubleshooting) section below.

---

# Advanced Configuration (For Technical Teams)

This section is for teams with technical expertise who want more control over their deployment.

## Manual Configuration

If you prefer not to use the setup script, you can configure ArgusPAM manually.

### 1. Create Environment File

```bash
# Copy template
cp env.template .env

# Edit with your preferred editor
nano .env
# or
vim .env
```

### 2. Required Configuration

Fill in these required values:

```bash
# Database (generate secure passwords)
DB_ROOT_PASSWORD=your_secure_root_password
DB_PASSWORD=your_secure_user_password

# Laravel App Key (generate with: openssl rand -base64 32)
APP_KEY=base64:your_generated_key_here

# Domains
APP_URL=https://api.arguspam.com
APP_WEB_URL=https://arguspam.com
WEB_ORIGIN=https://arguspam.com
PUBLIC_API_URL=https://api.arguspam.com
SANCTUM_STATEFUL_DOMAINS=arguspam.com
CORS_ALLOWED_ORIGINS=https://arguspam.com

# SMTP
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password
MAIL_FROM_ADDRESS=noreply@arguspam.com

# OpenAI
OPENAI_API_KEY=sk-your_key_here
OPENAI_ORGANIZATION=org-your_org_id

# Admin
EMAIL_DEFAULT=admin@arguspam.com
EMAIL_SUPPORT=support@arguspam.com
```

### 3. Optional: Customize Resources

Load size-specific configurations:

**Small Server:**
```bash
# Copy resource limits from small template
grep -E "^(MYSQL_|REDIS_|API_|HORIZON_|WEB_)" env.prod.small.example >> .env
```

**Large Server:**
```bash
# Copy resource limits from large template
grep -E "^(MYSQL_|REDIS_|API_|HORIZON_|WEB_)" env.prod.large.example >> .env
```

For full variable reference, see [DOCKER_ENV_VARIABLES.md](DOCKER_ENV_VARIABLES.md).

### 4. Deploy

```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

---

## SSL/HTTPS Setup

For production, you need SSL/TLS certificates for HTTPS. Here are three options:

### Option 1: Cloudflare (Easiest)

If you're using Cloudflare for DNS:

1. In Cloudflare dashboard, go to **SSL/TLS**
2. Set SSL mode to **"Full (strict)"**
3. Enable **"Always Use HTTPS"**
4. Cloudflare handles SSL automatically!

**Pros:** Free, automatic, easy
**Cons:** Traffic goes through Cloudflare

### Option 2: Traefik (Recommended for Self-Hosted)

Traefik automatically obtains and renews Let's Encrypt certificates.

1. Create `traefik` directory:
```bash
mkdir traefik
cd traefik
```

2. Create `docker-compose.traefik.yml`:
```yaml
version: '3.8'

services:
  traefik:
    image: traefik:v2.10
    container_name: traefik
    restart: always
    command:
      - "--api.dashboard=true"
      - "--providers.docker=true"
      - "--entrypoints.web.address=:80"
      - "--entrypoints.websecure.address=:443"
      - "--certificatesresolvers.letsencrypt.acme.email=admin@arguspam.com"
      - "--certificatesresolvers.letsencrypt.acme.storage=/letsencrypt/acme.json"
      - "--certificatesresolvers.letsencrypt.acme.httpchallenge.entrypoint=web"
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - "/var/run/docker.sock:/var/run/docker.sock:ro"
      - "./letsencrypt:/letsencrypt"
    networks:
      - arguspam-network

networks:
  arguspam-network:
    external: true
```

3. Update ArgusPAM compose file to use Traefik labels (already configured in `docker-compose.prod.yml`)

4. Start Traefik:
```bash
docker compose -f docker-compose.traefik.yml up -d
```

### Option 3: Nginx + Certbot (Manual)

For those who prefer Nginx:

1. Install Nginx and Certbot:
```bash
apt install -y nginx certbot python3-certbot-nginx
```

2. Create Nginx config `/etc/nginx/sites-available/arguspam`:
```nginx
server {
    server_name arguspam.com www.arguspam.com;
    location / {
        proxy_pass http://localhost:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}

server {
    server_name api.arguspam.com;
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

3. Enable and get certificate:
```bash
ln -s /etc/nginx/sites-available/arguspam /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
certbot --nginx -d arguspam.com -d api.arguspam.com
```

Certbot will automatically configure SSL and set up auto-renewal.

---

## Production Hardening

Security checklist for production deployments:

### 1. Firewall Configuration

```bash
# Install UFW (Ubuntu Firewall)
apt install -y ufw

# Allow SSH
ufw allow ssh

# Allow HTTP and HTTPS
ufw allow 80/tcp
ufw allow 443/tcp

# Enable firewall
ufw enable
```

### 2. Secure Database

Update `.env` with strong passwords:
```bash
# Generate strong passwords
DB_ROOT_PASSWORD=$(openssl rand -base64 32)
DB_PASSWORD=$(openssl rand -base64 32)
```

### 3. Enable Application Firewall

In your `.env`:
```bash
APP_DEBUG=false
APP_ENV=production
```

### 4. Regular Updates

```bash
# Update system
apt update && apt upgrade -y

# Update Docker images
cd arguspam
docker compose pull
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### 5. Backup Strategy

**Automated Backups:**

Create `/root/backup-arguspam.sh`:
```bash
#!/bin/bash
BACKUP_DIR="/root/backups"
DATE=$(date +%Y%m%d-%H%M%S)

mkdir -p $BACKUP_DIR

# Backup database
docker compose exec -T mysql mysqldump -u arguspam -p$DB_PASSWORD arguspam > $BACKUP_DIR/db-$DATE.sql

# Backup .env file
cp .env $BACKUP_DIR/env-$DATE

# Backup uploaded files (if any)
docker compose exec -T api tar czf - /var/www/html/storage > $BACKUP_DIR/storage-$DATE.tar.gz

# Keep only last 7 days
find $BACKUP_DIR -mtime +7 -delete
```

Schedule with cron:
```bash
chmod +x /root/backup-arguspam.sh
crontab -e
# Add: 0 2 * * * /root/backup-arguspam.sh
```

### 6. Monitoring

**Check resource usage:**
```bash
docker stats
```

**Set up alerts (optional):**
- Use monitoring tools like Prometheus + Grafana
- Configure email alerts for high resource usage
- Monitor disk space: `df -h`

---

## Scaling & Performance

### When to Upgrade Server Size

Monitor these metrics:

**CPU Usage:**
```bash
docker stats --no-stream | grep cpu
```
- If consistently > 70%, consider upgrading

**Memory Usage:**
```bash
free -h
docker stats --no-stream | grep MEM
```
- If consistently > 80%, upgrade or adjust limits

**Response Time:**
- If page load > 3 seconds, investigate

### Adjusting Resources Dynamically

**Scale up MySQL:**
```bash
# Edit .env
MYSQL_CPU_LIMIT=4
MYSQL_MEMORY_LIMIT=4G

# Restart
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d mysql
```

**Scale up API:**
```bash
# Edit .env
API_CPU_LIMIT=4
API_MEMORY_LIMIT=2G

# Restart
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d api
```

For detailed resource tuning, see [DOCKER_ENV_VARIABLES.md](DOCKER_ENV_VARIABLES.md).

### Load Balancing (For 5000+ Users)

For very large deployments:

1. **Separate Database Server:** Move MySQL to dedicated server
2. **Redis Cluster:** For high-availability caching
3. **Multiple App Servers:** Load balance across several API/Web instances
4. **CDN:** Use Cloudflare or AWS CloudFront for static assets

Contact support for enterprise architecture guidance.

---

## Troubleshooting

### Services Won't Start

**Check logs:**
```bash
docker compose logs
```

**Common causes:**
- Port conflicts: Another service using ports 3000 or 8000
- Insufficient memory: Check `docker stats`
- Invalid configuration: Validate `.env` file

**Solution:**
```bash
# Stop everything
docker compose down

# Remove old containers
docker compose rm -f

# Restart
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Database Connection Errors

**Error:** `SQLSTATE[HY000] [2002] Connection refused`

**Causes:**
- MySQL not ready yet
- Wrong database credentials

**Solution:**
```bash
# Check MySQL status
docker compose ps mysql
docker compose logs mysql

# Wait for MySQL to be healthy
docker compose exec mysql mysql -uroot -p$DB_ROOT_PASSWORD -e "SELECT 1"

# Restart API
docker compose restart api
```

### Can't Access Web Interface

**Check DNS:**
```bash
ping arguspam.com
```
Should show your server IP.

**Check firewall:**
```bash
# On server
curl http://localhost:3000
curl http://localhost:8000

# If works locally but not externally, open ports:
ufw allow 3000/tcp
ufw allow 8000/tcp
```

**Check containers:**
```bash
docker compose ps
docker compose logs web
```

### High Memory Usage

**Check usage:**
```bash
docker stats
```

**Solutions:**

1. **Reduce MySQL memory:**
```bash
# In .env
MYSQL_MEMORY_LIMIT=1G
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d mysql
```

2. **Reduce Redis memory:**
```bash
# In .env
REDIS_MEMORY_LIMIT=256M
REDIS_MAXMEMORY=256mb
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d redis
```

3. **Upgrade server** if limits are already minimal

### Slow Performance

**Check database:**
```bash
# Database size
docker compose exec mysql mysql -uroot -p$DB_ROOT_PASSWORD -e "
SELECT table_schema, 
       SUM(data_length + index_length) / 1024 / 1024 AS 'Size (MB)'
FROM information_schema.tables 
GROUP BY table_schema;"

# Optimize tables
docker compose exec mysql mysqlcheck -u root -p$DB_ROOT_PASSWORD --optimize --all-databases
```

**Check Redis:**
```bash
docker compose exec redis redis-cli INFO memory
```

**Enable caching:**
In `.env`:
```bash
CACHE_STORE=redis
SESSION_DRIVER=redis
```

### Disk Space Issues

**Check usage:**
```bash
df -h
```

**Clean up:**
```bash
# Remove old Docker images
docker system prune -a

# Remove old logs
docker compose logs --tail=0

# Check large log files
du -sh /var/lib/docker/containers/*/*-json.log

# Reduce log retention (in .env)
MYSQL_LOG_MAX_FILE=2
API_LOG_MAX_FILE=3
```

### Email Not Sending

**Test SMTP:**
```bash
docker compose exec api php artisan tinker

# In tinker:
Mail::raw('Test email', function($msg) {
    $msg->to('your-email@example.com')->subject('Test');
});
```

**Check logs:**
```bash
docker compose logs api | grep -i mail
```

**Common issues:**
- Wrong SMTP credentials
- Firewall blocking port 587
- Need App Password for Gmail

### OpenAI API Errors

**Error:** `Invalid API key`
- Check `.env` has correct `OPENAI_API_KEY`
- Verify key at [platform.openai.com/api-keys](https://platform.openai.com/api-keys)

**Error:** `Rate limit exceeded`
- You've hit usage limits
- Upgrade OpenAI plan or wait

**Error:** `Insufficient quota`
- Add payment method at OpenAI
- Check billing at [platform.openai.com/account/billing](https://platform.openai.com/account/billing)

### Getting Help

1. **Check logs:** `docker compose logs -f`
2. **Review documentation:**
   - [DOCKER_ENV_VARIABLES.md](DOCKER_ENV_VARIABLES.md) - All configuration options
   - [QUICK_REFERENCE.md](QUICK_REFERENCE.md) - Command reference
3. **Check GitHub Issues:** Search for similar problems
4. **Contact Support:** Provide logs and error messages

---

## Quick Reference Links

- **Getting Started:** [QUICK_START.md](QUICK_START.md)
- **Environment Variables:** [DOCKER_ENV_VARIABLES.md](DOCKER_ENV_VARIABLES.md)
- **Command Reference:** [QUICK_REFERENCE.md](QUICK_REFERENCE.md)
- **Back to Main README:** [../README.md](../README.md)

---

## Success Checklist

Your deployment is complete when:

- [ ] All Docker containers are running and healthy
- [ ] You can access the web interface via your domain
- [ ] You can log in with admin credentials
- [ ] Email notifications are working (test with password reset)
- [ ] SSL/HTTPS is configured and working
- [ ] Backups are configured and tested
- [ ] You've saved all passwords and credentials securely

**Congratulations! ArgusPAM is now deployed and ready to use.** 🎉
