# ArgusPAM

> 🛠️ **Status:** Currently under active development | [Visit Website](https://arguspam.com) | [GitHub](https://github.com/lunzai/arguspam)

**Argus Privilege Access Management (ArgusPAM)** - Open-source database credential management with AI-assisted security. Because nobody likes a data breach! 🔐

## The Name

Our name comes from Argus Panoptes, the all-seeing giant from Greek mythology with a hundred eyes. Legend says he was so vigilant that only some of his eyes would sleep at a time - the others stayed wide open, watching everything. Just like our mythical namesake, ArgusPAM keeps constant watch over your database access. 🛡️

---

## What is ArgusPAM?

ArgusPAM is a **Privileged Access Management (PAM) solution specifically designed for databases**. It acts as a secure gateway between your team and your databases, ensuring that:

- **The right people** have access to the right databases
- **Access is temporary** and granted only when needed (Just-In-Time)
- **Every action is logged** and can be audited
- **AI watches** for unusual behavior and security risks
- **Credentials are never shared** directly with users

### Think of it as a smart security guard for your databases

Instead of giving everyone direct database credentials (which is risky), ArgusPAM sits in the middle, managing who can access what, when, and for how long. It's like having a bouncer at a club who checks IDs, keeps a guest list, and remembers everyone who came in.

---

## 🤔 Why Do You Need Database PAM?

### Without ArgusPAM (Traditional Approach)

**❌ The Problem:**
- Shared database credentials across the team
- No visibility into who accessed what
- Credentials stored in wikis, Slack, sheets, or password managers
- When someone leaves, you must rotate all credentials
- No way to track what changes were made
- Difficult to meet compliance requirements (SOC 2, ISO 27001, GDPR)
- One compromised credential = full database access

**Real-world scenario:**
> "Hey, what's the production database password?" 
> "Check the #engineering Slack channel from 6 months ago"
> 💥 *Anyone with Slack access now has production database access*

### With ArgusPAM

**✅ The Solution:**
- **Individual access tracking** - Know exactly who accessed which database
- **Time-limited access** - Access expires automatically after X hours
- **Zero shared credentials** - Each person gets their own session
- **Complete audit trail** - Every query, every connection is logged
- **AI-powered alerts** - Unusual patterns trigger notifications
- **Instant revocation** - Remove access immediately when someone leaves
- **Compliance ready** - Meets SOC 2, ISO 27001, HIPAA requirements
- **Multi-factor authentication** - Extra security layer for sensitive data

**Real-world scenario:**
> "I need production database access for 2 hours to debug issue #123"
> *Clicks request → Manager approves → Access granted for 2 hours only*
> ✅ *Automatic audit log + AI monitoring + access expires automatically*

---

## 🎯 Who Should Use ArgusPAM?

### Perfect For:

**🏢 Startups & SMEs (5-500 employees)**
- Growing team that needs proper database security
- Preparing for SOC 2 or ISO 27001 certification
- Want to implement security best practices early
- Need audit trails for compliance

**🏥 Healthcare & Finance**
- Must comply with HIPAA, PCI-DSS, or SOX
- Handle sensitive patient or financial data
- Need detailed access logs for audits
- Require strict access controls

**🔐 Security-Conscious Organizations**
- Take security seriously (as you should!)
- Want to implement Zero Trust principles
- Need granular access control
- Want AI-assisted threat detection

**👥 Distributed Teams**
- Remote employees accessing databases
- Need context-aware access (location, time, device)
- Want to limit access based on IP or location
- Multiple timezones requiring temporary access

### Not Ideal For:

- Solo developers or very small teams (1-3 people) - overhead might not be worth it
- Organizations without databases (obviously!)
- Teams with read-only database access needs (simpler solutions exist)
- Projects without compliance or security requirements (though you should still care about security!)

---

## 💡 Why We Built ArgusPAM

### The Problem We Saw

Most database PAM solutions are:
- 💰 **Expensive** - $50k-$500k+ per year (enterprise pricing)
- 🏢 **Enterprise-only** - Designed for Fortune 500, not startups
- 🤯 **Complex** - Require dedicated security teams to operate
- 🔒 **Closed-source** - Vendor lock-in, no customization
- 🐌 **Slow** - Take months to deploy and configure

**Meanwhile**, small and medium businesses are:
- Sharing database credentials in Slack
- Using shared admin accounts
- Unable to track who did what
- Failing compliance audits
- Getting hacked due to compromised credentials

### Our Solution

ArgusPAM brings enterprise-grade database security to **everyone**:

- 🆓 **Open Source** - Free to use, modify, and deploy
- 💪 **SME-Friendly** - Designed for teams of 5-500 people
- ⚡ **Quick Setup** - Deploy in 15-30 minutes
- 🧠 **AI-Powered** - Smart security without complexity
- 🛠️ **Self-Hosted** - Your data stays on your infrastructure
- 📊 **Modern Stack** - Built with latest technologies (Laravel, Svelte)

We believe that **every organization deserves proper database security**, not just those who can afford six-figure contracts.

---

## 💎 Key Features

### 🔐 Security & Access Control
- **Just-In-Time (JIT) Access** - Temporary access that expires automatically
- **Role-Based Access Control (RBAC)** - Different permissions for different roles
- **Principle of Least Privilege** - Give only what's needed, nothing more
- **Multi-Factor Authentication (MFA)** - Extra security for sensitive operations
- **Session Recording** - See exactly what was done during each session

### 🤖 AI-Assisted Security
- **Anomaly Detection** - AI spots unusual access patterns
- **Risk Scoring** - Real-time risk assessment for each request
- **Smart Alerts** - Get notified about suspicious activity
- **Behavioral Analysis** - Learn normal patterns, flag anomalies
- **Automated Recommendations** - AI suggests security improvements

### 📊 Compliance & Auditing
- **Complete Audit Trail** - Every connection, every query logged
- **Compliance Reports** - SOC 2, ISO 27001, HIPAA ready
- **Access Reviews** - Regular reviews of who has access to what
- **Change Tracking** - Know who made what changes
- **Exportable Logs** - Download logs for external audits

### 🌍 Context-Aware Access (Coming soon)
- **Location-Based** - Restrict access by IP or geographic location
- **Time-Based** - Allow access only during business hours
- **Device Fingerprinting** - Track which devices are used
- **Connection Monitoring** - Real-time view of active sessions

### 👥 User Experience
- **Self-Service Portal** - Request access without tickets
- **Approval Workflows** - Managers approve access requests
- **Email Notifications** - Stay informed about access requests
- **Easy Onboarding** - New team members get access in minutes

### 🏗️ Technical Features
- **Multi-Database Support** - MySQL, PostgreSQL, MongoDB (coming soon)
- **REST API** - Integrate with your existing tools
- **Docker-Based** - Easy deployment and scaling
- **High Availability** - Run multiple instances for redundancy
- **Backup & Restore** - Built-in backup capabilities

---

## 🚀 Quick Start (5 Minutes)

Get ArgusPAM running with one command:

```bash
./setup.sh
```

The setup script will:
- ✓ Auto-generate secure database passwords
- ✓ Auto-generate Laravel application key
- ✓ Ask you for essential configuration (domain, SMTP, OpenAI)
- ✓ Create a ready-to-use `.env` file
- ✓ Show you the next steps

**Then start the application:**
```bash
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Wait ~30 seconds for containers to be healthy, then run installation:
docker exec -it arguspam-api php artisan pam:install
```

**→ [Full Quick Start Guide](docs/QUICK_START.md)**

---

## 📚 Documentation

### Choose Your Path

#### 🌟 **For Everyone (No Technical Background Required)**

**Start Here:**
1. **[Quick Start Guide](docs/QUICK_START.md)** ⚡
   - 3 simple methods to get started
   - Interactive setup script walkthrough
   - Time: 5-10 minutes

2. **[Complete Deployment Guide](docs/DEPLOYMENT.md)** 📖
   - Step-by-step from zero to production
   - Server setup (DigitalOcean, AWS Lightsail, AWS EC2)
   - Domain & DNS configuration with Cloudflare
   - SSL/HTTPS setup options
   - Time: 15-30 minutes

#### 🔧 **For Technical Teams**

**For Advanced Configuration:**
1. **[Docker Environment Variables](docs/DOCKER_ENV_VARIABLES.md)** ⚙️
   - Complete variable reference (150+ options)
   - Resource allocation guide
   - Scaling recommendations

2. **[Quick Reference](docs/QUICK_REFERENCE.md)** 📋
   - Command cheat sheet
   - Common operations
   - Troubleshooting quick fixes

3. **[Manual Configuration](docs/DEPLOYMENT.md#manual-configuration)** 🛠️
   - Manual setup without script
   - Production hardening
   - Custom configurations

---

## 🤔 Which Guide Should I Read?

**I just want to get it running fast:**
→ Run `./setup.sh` (5 min)

**I'm deploying for the first time:**
→ [DEPLOYMENT.md](docs/DEPLOYMENT.md) - Complete step-by-step guide (30 min)

**I need specific commands:**
→ [QUICK_REFERENCE.md](docs/QUICK_REFERENCE.md) - Command reference

**I want to customize resources:**
→ [DOCKER_ENV_VARIABLES.md](docs/DOCKER_ENV_VARIABLES.md) - All configuration options

**I'm having issues:**
→ [DEPLOYMENT.md - Troubleshooting](docs/DEPLOYMENT.md#troubleshooting)

---

## 💡 Key Features

- **Principle of Least Privilege (PoLP)** - Only get what you need, nothing more
- **Just-In-Time Access (JIT)** - Temporary superpowers when you need them
- **Role-Based Access Control (RBAC)** - The right permissions for the right people
- **AI-Assisted Security** - Smart algorithms watching your back
- **Multi-Factor Authentication (MFA)** - Because passwords alone are so 2010
- **User Contextual Awareness** - Location and behavior tracking (in a non-creepy way)
- **Complete Audit Trail** - We remember everything

---

## 📊 Server Sizing Guide

Choose based on your team size and expected usage:

| Size | CPU | RAM | Monthly Cost | Concurrent Users | Team Size | Use Case |
|------|-----|-----|--------------|------------------|-----------|----------|
| **Small** | 2 cores | 4GB | $20-40 | 50-200 | 5-20 people | Small teams, testing |
| **Medium** | 4 cores | 8GB | $40-80 | 200-1000 | 20-100 people | SMEs, growing companies |
| **Large** | 8 cores | 16GB | $80-160 | 1000-5000 | 100-500 people | Large organizations |

**Not sure?** Start with **Medium** - you can always scale up or down later.

Detailed capacity estimates in [`env.prod.*.example`](env.prod.medium.example) files.

---

## 🎯 Deployment Options

### Option 1: Interactive Setup (Recommended)
```bash
# Clone repository
git clone https://github.com/lunzai/arguspam.git
cd arguspam

# Run setup script
./setup.sh

# Start ArgusPAM
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Option 2: Manual Configuration
```bash
# Clone repository
git clone https://github.com/lunzai/arguspam.git
cd arguspam

# Copy and edit configuration
cp env.template .env
nano .env

# Start ArgusPAM
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d
```

### Option 3: Development/Testing
```bash
# Clone repository
git clone https://github.com/lunzai/arguspam.git
cd arguspam

# Start in development mode (auto-setup)
docker compose up -d
```

---

## 🔗 Important Files

| File | Purpose |
|------|---------|
| `setup.sh` | Interactive setup script |
| `env.template` | Configuration template |
| `env.prod.small.example` | Small server configuration |
| `env.prod.medium.example` | Medium server configuration (recommended) |
| `env.prod.large.example` | Large server configuration |
| `docker-compose.yml` | Base Docker configuration |
| `docker-compose.override.yml` | Development overrides |
| `docker-compose.prod.yml` | Production overrides |

---

## 🆘 Getting Help

### Quick Troubleshooting

**Services won't start:**
```bash
docker compose logs
docker compose down
docker compose up -d
```

**Can't access the web interface:**
- Access via IP: `http://YOUR_SERVER_IP:3000`
- Check logs: `docker compose logs web`
- Check firewall: Ensure ports 3000, 8000 are open

**Database connection errors:**
```bash
docker compose ps mysql
docker compose logs mysql
docker compose restart api
```

### Documentation

- **[Troubleshooting Guide](docs/DEPLOYMENT.md#troubleshooting)** - Common issues and solutions
- **[Quick Reference](docs/QUICK_REFERENCE.md)** - Handy commands
- **[FAQ](docs/DEPLOYMENT.md)** - Frequently asked questions

### Community & Support

- **GitHub Issues:** [Report bugs or request features](https://github.com/lunzai/arguspam/issues)
- **Website:** [arguspam.com](https://arguspam.com)
- **Email:** support@arguspam.com

---

## 🛠️ Tech Stack

- **Backend:** Laravel 11 (PHP 8.3)
- **Frontend:** SvelteKit (TypeScript)
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis 7
- **Deployment:** Docker + Docker Compose
- **AI:** OpenAI GPT-5

---

## 📝 License

AGPL-3.0 License - See [LICENSE](LICENSE) file for details.

This means ArgusPAM is free and open-source, but if you modify it and deploy publicly, you must share your modifications.

---

## 🤝 Contributing

We welcome contributions! Whether it's:
- 🐛 Bug reports
- 💡 Feature requests
- 📖 Documentation improvements
- 🔧 Code contributions

Please open an issue or submit a pull request on [GitHub](https://github.com/lunzai/arguspam).