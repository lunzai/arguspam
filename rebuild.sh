#!/bin/bash
# Complete Docker rebuild script

echo "🛑 Stopping all containers..."
docker compose -f docker-compose.yml -f docker-compose.prod.yml down

echo "🗑️  Removing all volumes (data will be lost)..."
docker compose -f docker-compose.yml -f docker-compose.prod.yml down -v

echo "🧹 Removing images..."
docker rmi arguspam-api arguspam-horizon arguspam-web 2>/dev/null || true

echo "🔨 Rebuilding images (no cache)..."
docker compose -f docker-compose.yml -f docker-compose.prod.yml build --no-cache

echo "🚀 Starting containers..."
docker compose -f docker-compose.yml -f docker-compose.prod.yml up -d

echo "⏳ Waiting for containers to be healthy..."
sleep 15

echo "📊 Container status:"
docker compose -f docker-compose.yml -f docker-compose.prod.yml ps

echo ""
echo "✅ Rebuild complete!"
echo ""
echo "🔧 Next step - Complete installation:"
echo ""
echo "Run the installation wizard to set up your first organization and admin user:"
echo "  docker exec -it arguspam-api php artisan pam:install"
echo ""
echo "Note: Migrations run automatically on container startup ✓"
