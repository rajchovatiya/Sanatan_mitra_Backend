# Progress and next steps

Update this file at the end of every work session. Last update: 2026-11-08.

## Done (40 tests passing)
- [x] Foundation: Laravel 13, Sanctum, rate limits, JSON errors, docs (API.md, DEPLOYMENT.md), Git
- [x] OTP login, profile, photo/logo upload, devices, account deletion, saved kundlis, app config
- [x] Astrology endpoints (Panchang/Kundli/Matching) behind a provider interface (provider not connected)
- [x] Admin panel at `/admin`: login (`admins` table, roles super_admin/editor, 5 tries/min), dashboard
- [x] Categories (one table, `type` = poster | mantra; add a type in `Category::TYPES` for new modules)
- [x] Posters: admin upload, plus app API list by category
- [x] Mantras: admin upload with deity image and audio, plus app API list and detail
- [x] Content text in en/hi/gu (json columns, `HasTranslations::tr`), `Accept-Language` picks the language
- [x] Media helper `App\Support\Media` (`MEDIA_DISK`), so moving to cloud storage is a settings change

## Next (in this order)
1. MySQL: user fills `DB_PASSWORD` in `.env`, then run
   `CREATE DATABASE sanatan_mitra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`, `php artisan migrate`,
   `php artisan storage:link`, `php artisan admin:create <email> "<name>"`
2. Raise PHP upload limits for audio (php.ini: `upload_max_filesize=25M`, `post_max_size=30M`; Nginx `client_max_body_size 30m`)
3. Admin audit log (who changed what) and admin user management (super_admin only, gate `manage-admins`)
4. More content modules, same pattern (migration, model with `HasTranslations`, admin controller, form, app API, tests):
   Festivals/Vrat, Rashifal, Temples/Darshan, Library (Katha/Aarti/Chalisa), Gita, Suvichar, Muhurat, Guides,
   Wallpapers/Videos, Live/Courses
5. `/home` combined endpoint, search, favorites, notifications (FCM sending from admin), feedback, pages
6. Admin: app users list/block (blocking must also delete their tokens), settings screen for app config
7. Connect the Flutter app module by module (replace `lib/data/mock_*`)
8. Before launch: SMS provider, astrology provider, cloud storage for media, server setup per DEPLOYMENT.md

## Waiting on the user
- MySQL password in `.env`
- Astrology provider docs + key
- SMS provider choice
