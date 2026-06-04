#!/bin/bash
set -e

if [ ! -f .env ]; then
    echo "==> Copying .env.example to .env..."
    cp .env.example .env
fi

if command -v composer > /dev/null 2>&1; then
    echo "==> Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if grep -q "^APP_KEY=$" .env; then
    echo "==> Generating application key..."
    php artisan key:generate --force
fi

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
