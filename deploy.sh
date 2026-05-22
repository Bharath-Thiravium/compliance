#!/bin/bash
set -e

cd "$(dirname "$0")"

echo "Pulling latest code..."
git config pull.rebase false
git pull origin main

echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "Clearing old caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

echo "Running migrations..."
php artisan migrate --force

echo "Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Setting permissions..."
chmod -R 755 storage bootstrap/cache
chmod -R 644 storage/logs

echo "Creating storage symlink if missing..."
php artisan storage:link --force 2>/dev/null || true

echo "Deployed successfully."
