#!/bin/sh

# Run Laravel setup commands
echo "Clearing config cache..."
php artisan config:clear

echo "Running migrations..."
php artisan migrate --force

echo "Running seeders (if needed)..."
php artisan db:seed --force || true

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Caching views..."
php artisan view:cache

echo "Starting supervisor (PHP-FPM + Nginx)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
