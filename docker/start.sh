#!/bin/sh
# Container start: listen on Railway's port, prepare storage, migrate, serve.
set -e

# mod_php needs exactly one MPM: prefork ("More than one MPM loaded" otherwise).
rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
[ -e /etc/apache2/mods-enabled/mpm_prefork.load ] || a2enmod mpm_prefork >/dev/null

PORT="${PORT:-8080}"
sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# The uploads volume is mounted at storage/app/public and may start empty.
mkdir -p storage/app/public storage/app/private storage/logs \
    storage/framework/cache storage/framework/sessions storage/framework/views
chown -R www-data:www-data storage bootstrap/cache

php artisan storage:link 2>/dev/null || true

# Railway's private network (and MySQL) can need a little time after a start.
tries=0
until php artisan migrate --force; do
    tries=$((tries + 1))
    if [ "$tries" -ge 24 ]; then
        echo "Database still not reachable after 2 minutes; check DB_HOST and that MySQL is running."
        exit 1
    fi
    echo "Database not ready, retrying in 5 seconds ($tries/24)"
    sleep 5
done

# First admin from FIRST_ADMIN_* variables (only while no admin exists).
php artisan admin:bootstrap || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec apache2-foreground
