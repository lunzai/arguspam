#!/bin/bash
set -e

echo "================================================"
echo "🚀 ArgusPAM UAT Deployment"
echo "================================================"

cd /var/www/arguspam

# Save current commit for potential rollback
PREV_COMMIT=$(git rev-parse HEAD)
echo "Previous commit: $PREV_COMMIT" > /tmp/arguspam_prev_deploy

# Fetch and reset to match remote exactly
echo "📥 Fetching latest code..."
git fetch origin uat/demo

echo "🔄 Resetting to origin/uat/demo..."
git reset --hard origin/uat/demo
git clean -fd

NEW_COMMIT=$(git rev-parse HEAD)
echo "Now at commit: $NEW_COMMIT"

# Deploy API
echo "🔧 Deploying API..."
cd api
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize

# Deploy Web
echo "🌐 Deploying Web..."
cd ../web
npm ci --production
npm run build

# Restart services
echo "🔄 Restarting services..."
sudo systemctl restart arguspam-horizon
sudo systemctl restart arguspam-web
sudo systemctl reload php8.3-fpm

# Health check
sleep 5
echo "🏥 Health check..."
if curl -f -s https://demoapi.arguspam.com/up > /dev/null; then
  echo "✅ Deployment successful!"
  echo "✅ Previous: $PREV_COMMIT"
  echo "✅ Current:  $NEW_COMMIT"
else
  echo "❌ Health check failed! Rolling back..."
  git reset --hard $PREV_COMMIT
  exit 1
fi