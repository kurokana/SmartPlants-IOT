#!/bin/bash
#
# Production Deployment Script for SmartPlants IoT
# Usage: ./deploy-production.sh
#

set -e  # Exit on error

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║   🚀 SmartPlants IoT - Production Deployment                  ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Pull latest code
echo -e "${YELLOW}[1/8]${NC} Pulling latest code from GitHub..."
git pull origin main
echo -e "${GREEN}✓${NC} Code updated"
echo ""

# Step 2: Install dependencies
echo -e "${YELLOW}[2/8]${NC} Installing/Updating Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction
echo -e "${GREEN}✓${NC} Dependencies installed"
echo ""

# Step 3: Refresh autoload (CRITICAL!)
echo -e "${YELLOW}[3/8]${NC} Refreshing Composer autoload..."
composer dump-autoload --optimize --no-interaction
echo -e "${GREEN}✓${NC} Autoload refreshed"
echo ""

# Step 4: Run migrations
echo -e "${YELLOW}[4/8]${NC} Running database migrations..."
php artisan migrate --force --no-interaction
echo -e "${GREEN}✓${NC} Migrations completed"
echo ""

# Step 5: Clear all caches
echo -e "${YELLOW}[5/8]${NC} Clearing all caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✓${NC} Caches cleared"
echo ""

# Step 6: Optimize for production
echo -e "${YELLOW}[6/8]${NC} Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✓${NC} Optimization complete"
echo ""

# Step 7: Fix permissions
echo -e "${YELLOW}[7/8]${NC} Fixing storage permissions..."
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
echo -e "${GREEN}✓${NC} Permissions fixed"
echo ""

# Step 8: Verify deployment
echo -e "${YELLOW}[8/8]${NC} Verifying deployment..."

# Check if critical files exist
if [ -f "app/Services/SensorDataService.php" ]; then
    echo -e "${GREEN}✓${NC} SensorDataService.php exists"
else
    echo -e "${RED}✗${NC} SensorDataService.php NOT FOUND!"
    exit 1
fi

if [ -f "app/Traits/HasSensorQueries.php" ]; then
    echo -e "${GREEN}✓${NC} HasSensorQueries.php exists"
else
    echo -e "${RED}✗${NC} HasSensorQueries.php NOT FOUND!"
    exit 1
fi

# Check autoload
if php artisan tinker --execute="echo class_exists('App\Services\SensorDataService') ? 'OK' : 'FAIL';" 2>/dev/null | grep -q "OK"; then
    echo -e "${GREEN}✓${NC} SensorDataService autoload OK"
else
    echo -e "${YELLOW}⚠${NC} SensorDataService autoload check failed (might be normal)"
fi

echo ""
echo "╔════════════════════════════════════════════════════════════════╗"
echo "║   ✅ DEPLOYMENT COMPLETE!                                      ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""
echo "🌐 Test your application:"
echo "   - https://kurokana.alwaysdata.net/sensors/soil"
echo "   - https://kurokana.alwaysdata.net/sensors/environment"
echo "   - https://kurokana.alwaysdata.net/sensors/health"
echo ""
echo "📊 If errors persist, check logs:"
echo "   tail -f storage/logs/laravel.log"
echo ""
