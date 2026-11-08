# Deployment checklist

For about 1000 users a day, one small VPS (2 vCPU, 4 GB RAM) running Nginx, PHP-FPM and MySQL is enough.

## Server
- Ubuntu LTS, PHP 8.4-FPM with `pdo_mysql, mbstring, openssl, fileinfo, gd, intl, zip, curl`, MySQL 8+, Nginx, Composer.
- No Node.js on the server.
- Firewall (ufw): allow 22 (SSH key only, no password login), 80 and 443. MySQL (3306) is **never** open to the internet.
- HTTPS with Let's Encrypt (certbot), with automatic renewal. Redirect HTTP to HTTPS.
- Nginx `root` points to `public/` only. Set `client_max_body_size 5m`.

## Application
```
composer install --no-dev --optimize-autoloader
cp .env.example .env    # then fill in the values below
php artisan key:generate
php artisan migrate --force
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```
`.env` for production: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://...`,
a separate MySQL user that can only use the app database (not root), `OTP_DRIVER=<sms provider>`, `OTP_EXPOSE_CODE=false`.
File permissions: only `storage/` and `bootstrap/cache/` are writable by the web user.

## Background work
- Queue worker under Supervisor: `php artisan queue:work --tries=3 --max-time=3600`
- Cron: `* * * * * php /path/artisan schedule:run`

## Backups
- Daily `mysqldump --single-transaction` of the database, plus `storage/app/public` (uploads), kept off the server
  (another server or cloud storage) for 14 days.
- Test a restore once a month.

## Logging and monitoring
- `LOG_STACK=daily`, `LOG_LEVEL=warning`, keep 14 days.
- Uptime check on `https://<server>/up`. Alert on 5xx errors and full disk.
- Enable the MySQL slow query log (queries over 1 second).

## Release and rollback
1. Back up the database.
2. Deploy each release into a new folder (`releases/<date>`) and point the `current` symlink at it, then reload PHP-FPM.
3. Run migrations. Every migration must have a working `down()`.
4. **Rollback:** point `current` back to the previous release, reload PHP-FPM, and run `php artisan migrate:rollback --step=N`
   only for migrations from that release. If data was damaged, restore the backup from step 1.
