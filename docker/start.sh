#!/bin/sh
# Container start: listen on Railway's port, prepare storage, migrate, serve.
set -e

PORT="${PORT:-8080}"
sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# The uploads volume is mounted at storage/app/public and may start empty.
mkdir -p storage/app/public storage/app/private storage/logs \
    storage/framework/cache storage/framework/sessions storage/framework/views
chown -R www-data:www-data storage bootstrap/cache

php artisan storage:link 2>/dev/null || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
