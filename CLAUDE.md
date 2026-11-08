# Sanatan Mitra Backend

Laravel API for the Sanatan Mitra Flutter app (separate project: `../Sanatan_Mitra`), plus the admin panel (to come).
The authoritative rules are in `docs/development-guidelines.pdf`. The short version follows. Ask before breaking any of them.

## Stack
- Laravel 13, PHP 8.4, MySQL, Nginx + PHP-FPM. Tests: PHPUnit (`php artisan test`). Style: `vendor/bin/pint`.
- Admin: custom Blade, plain CSS, vanilla JS only where needed.
- **Not allowed:** Node.js, npm/npx, Vite, Tailwind or any CSS framework, React/Vue, Filament, unnecessary packages.
- Redis and Reverb are not used. At the current load (about 1000 users a day) the database cache and queue are enough. Add them only with measured need.

## Structure
- API: `routes/api.php` (all under `/api/v1`), `app/Http/Controllers/Api/`, JSON only, Sanctum tokens, Form Requests in `app/Http/Requests/Api/`, API Resources in `app/Http/Resources/`.
- Admin (to come): `routes/web.php`, `app/Http/Controllers/Admin/`, `resources/views/admin/`, session login, separate `admins` table with roles.
- Shared: models, `app/Services/`, policies. Never duplicate logic between Admin and API.
- External providers sit behind interfaces chosen by env: `OtpSender` (`OTP_DRIVER`) and `AstrologyProvider` (`ASTROLOGY_DRIVER`).

## API conventions
- Success: `{"data": ...}` for resources, or a small flat object (login). Lists are paginated, except tiny per-user lists with a hard cap.
- Errors: `{"message": "..."}`, plus `"errors": {field: [...]}` for 422. A missing route or record, or someone else's record, gives `{"message": "Not found."}`.
- Breaking changes go to `/api/v2`. Never change `/v1` responses in a way that breaks a released app.
- Select only needed columns, eager-load, paginate, cache read-heavy content and clear it when admin edits.
- Every new endpoint: Form Request validation, policy or ownership check, rate limit if costly, feature test, entry in `docs/API.md`.

## Rules
- Migrations for every schema change. Never edit an already-deployed migration.
- Never commit `.env`, keys or credentials. Never run destructive DB commands without approval and a backup.
- Run `php artisan test` and `vendor/bin/pint --test` before reporting work as done.
