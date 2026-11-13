# Progress and next steps

Update this file at the end of every work session. Last update: 2026-11-13.

## Done (87 tests passing)
- Birth place search `/places` (GeoNames: every Indian village/town/city + world cities, ~580k rows via
  `php artisan places:import`; OpenStreetMap fallback). Kundli/Matching accept `timezone` for births abroad.
  The preview database already has the places loaded. App renamed to "Sanatan Shaily" (package id unchanged).
- Foundation: Laravel 13, Sanctum, rate limits, JSON errors, docs (API.md, DEPLOYMENT.md), Git
- App users: OTP login, profile, photo/logo, devices, account deletion, saved kundlis, favourites, notifications list
- App config (versions, maintenance, feature switches) editable in admin
- Astrology endpoints (Panchang/Kundli/Matching) connected to Prokerala (ASTROLOGY_DRIVER=prokerala, keys in .env), answers cached 30 days
- Content with admin screens and app API: Posters, Mantras (audio), Festivals & Vrat, Rashifal, Temples, Daily Darshan,
  Katha/Aarti/Beej/Chalisa (audio), Bhagavad Gita (18 chapters built in), Suvichar, Muhurat, Guides (gems, rudraksha,
  tarot, numerology, palmistry, graphology), Wallpapers, Video templates, Live, Courses
- `/home` in one cached call, search, feedback, pages (privacy/terms/about)
- Admin: sidebar menu, roles (super_admin / editor), app users with block, notifications (Firebase topics via queue),
  feedback inbox, settings, admin accounts, audit log of every change
- Content text in en/hi/gu (json columns stored unescaped, `HasTranslations::tr`), `Accept-Language` picks the language
- Media helper `App\Support\Media` (`MEDIA_DISK`), so moving to cloud storage is a settings change

## Local preview
- `.env` points at `database/preview.sqlite` (ignored by Git) because the running MySQL 9.7 (`C:\tools\mysql`)
  rejects the password in `.env`. The old MySQL80 service (8.0, unused since 2020) does not start; leave it stopped.
- Preview admin: `admin@sanatanmitra.local` (password given to the user in chat).
- Start the server from `public/` so uploads up to 25 MB work:
  `cd public; php -d upload_max_filesize=25M -d post_max_size=30M -S 127.0.0.1:8000 ..\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php`
- Notifications need a queue worker: `php artisan queue:work` (with `PUSH_DRIVER=log` they are only written to the log).

## Next
1. Connect the Flutter app to the API module by module (replace `lib/data/mock_*`); subscribe to `all_<lang>` push topics
2. MySQL: when the root password is known, set `DB_CONNECTION=mysql`, `DB_DATABASE=sanatan_mitra`, create the database
   (utf8mb4), `php artisan migrate`, `php artisan storage:link`, `php artisan admin:create <email> "<name>"`
3. Before launch: SMS provider (OTP_DRIVER), astrology provider (ASTROLOGY_DRIVER), Firebase key (PUSH_DRIVER=fcm),
   cloud storage for media (MEDIA_DISK), server setup per DEPLOYMENT.md (queue worker under Supervisor)

## Waiting on the user
- MySQL 9.7 root password
- (done) Prokerala keys added
- SMS provider choice
- Firebase project (service-account JSON) for push notifications
