# Sanatan Mitra Backend

API (and, later, the admin panel) for the Sanatan Mitra app. Laravel 13, PHP 8.4, MySQL.

- Rules for working on this project: `CLAUDE.md` and `docs/development-guidelines.pdf`
- All endpoints: `docs/API.md`
- Production setup, backups and rollback: `docs/DEPLOYMENT.md`

## Run locally
1. Create the database: `CREATE DATABASE sanatan_mitra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
2. Put the MySQL password in `.env` (`DB_PASSWORD=`). `.env` is never committed.
3. Run:
   ```
   composer install
   php artisan migrate
   php artisan storage:link
   php artisan serve
   ```
4. API: http://localhost:8000/api/v1/app/config

In development the login OTP is written to `storage/logs/laravel.log`, and is also returned as `debug_code`
because `OTP_EXPOSE_CODE=true`.

## Tests
`php artisan test` (uses an in-memory database, so it never touches your data) and `vendor/bin/pint --test` for code style.
