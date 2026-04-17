#!/bin/bash
set -e

echo "==> Installing Composer dependencies..."
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "==> Generating application key..."
php artisan key:generate --force

echo "==> Running migrations..."
php artisan migrate --force

echo "==> Clearing caches..."
php artisan config:clear
php artisan route:clear

echo "==> Setting storage permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "==> Starting PHP-FPM..."
exec "$@"
