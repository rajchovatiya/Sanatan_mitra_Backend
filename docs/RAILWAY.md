# Railway (test server)

Railway builds the `Dockerfile` (see `railway.json`). Each deploy runs
`docker/start.sh`: storage link, `migrate --force`, config/route/view cache, then Apache.

## One-time setup
1. New Project → Deploy from GitHub repo → `Sanatan_mitra_Backend`.
2. In the project: New → Database → **MySQL**.
3. Backend service → Settings → **Volumes** → mount path `/var/www/html/storage/app/public`
   (uploaded posters, photos and audio; Trial plan: 0.5 GB).
4. Backend service → Settings → Networking → **Generate Domain**.
5. Backend service → **Variables** (below), then redeploy.
6. After the first deploy, in the service shell:
   `php artisan admin:create <email> "<name>"` and, for birth-place search,
   `php artisan places:import` (~10 min, about 580,000 rows).

## Variables
| Name | Value |
|---|---|
| APP_NAME | Sanatan Shaily |
| APP_ENV | production |
| APP_DEBUG | false |
| APP_KEY | `base64:...` (generate with `php artisan key:generate --show`; keep secret) |
| APP_URL | `https://<generated-domain>` |
| TRUSTED_PROXIES | `*` |
| LOG_CHANNEL | stderr |
| DB_CONNECTION | mysql |
| DB_HOST | `${{MySQL.MYSQLHOST}}` |
| DB_PORT | `${{MySQL.MYSQLPORT}}` |
| DB_DATABASE | `${{MySQL.MYSQLDATABASE}}` |
| DB_USERNAME | `${{MySQL.MYSQLUSER}}` |
| DB_PASSWORD | `${{MySQL.MYSQLPASSWORD}}` |
| SESSION_DRIVER | database |
| CACHE_STORE | database |
| QUEUE_CONNECTION | sync (no worker on the test server) |
| FILESYSTEM_DISK | public |
| OTP_DRIVER | log |
| OTP_LENGTH | 4 (test) |
| OTP_TEST_CODE | 1234 (test only; empty before launch) |
| ASTROLOGY_DRIVER | prokerala |
| PROKERALA_CLIENT_ID / PROKERALA_CLIENT_SECRET | from the Prokerala dashboard (keep secret) |
| PUSH_DRIVER | log |

## Before launch
Empty `OTP_TEST_CODE`, set a real `OTP_DRIVER`, and move to the VPS in `DEPLOYMENT.md`
(queue worker, backups). The Trial plan stops after 30 days or $5: export the database
and the uploads volume before that.
