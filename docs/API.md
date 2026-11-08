# Sanatan Mitra API (v1)

Base URL: `https://<server>/api/v1`. Send `Accept: application/json` on every request.
Login-protected endpoints (🔑) need `Authorization: Bearer <token>`.

Status: ✅ built and tested · ⏳ planned

## Conventions
- Success: `{"data": ...}`. Paginated lists also return `links` and `meta`.
- Validation error (422): `{"message": "...", "errors": {"field": ["..."]}}`.
- Other errors: `{"message": "..."}` with the HTTP status. 401 not logged in, 403 not allowed or blocked,
  404 not found, 429 too many requests (see the `Retry-After` header), 503 provider not available.
- Dates `YYYY-MM-DD`, times `HH:MM` (24 h), `tz` is hours from UTC (India 5.5).
- `rashi` is 0 for Mesha through 11 for Meena. `language` is `en`, `hi` or `gu`.

## Rate limits
| Limit | Applies to |
|---|---|
| 120 / min per user (or IP) | every endpoint |
| 1 / min and 5 / hour per mobile, 30 / hour per IP | `auth/otp/send` |
| 10 / min per IP, 30 / hour per mobile | `auth/otp/verify` |
| 30 / min per user (or IP) | `astro/*` |

---

## App
| Status | Method | Path | Notes |
|---|---|---|---|
| ✅ | GET | `/app/config` | `android_min_version`, `ios_min_version`, `latest_version`, `maintenance`, `maintenance_message`, `support_email`, `support_whatsapp`, `features` (module to true/false) |
| ⏳ | GET | `/home` | Everything Home needs in one cached call |
| ⏳ | GET | `/search?q=` | |
| ⏳ | GET | `/pages/{slug}` | privacy, terms, about |

## Login
| Status | Method | Path | Notes |
|---|---|---|---|
| ✅ | POST | `/auth/otp/send` | `{mobile}` (10 digits). Returns `{message, expires_in}`. In test mode also `debug_code`. |
| ✅ | POST | `/auth/otp/verify` | `{mobile, code, device_name?}`. Returns `{token, is_new, user}`. Creates the account on first login. 403 if blocked. |
| ✅🔑 | POST | `/auth/logout` | `{fcm_token?}`. Ends this token; removes that device's push token. 204. |
| ✅🔑 | DELETE | `/account` | Deletes the user, tokens, devices, saved kundlis and uploaded images. 204. |

## Profile
| Status | Method | Path | Notes |
|---|---|---|---|
| ✅🔑 | GET | `/me` | User object (below) |
| ✅🔑 | PATCH | `/me` | Any of: `name, gender (male/female/other), birth_date, birth_time, birth_place, birth_lat, birth_lng, city, rashi, language, notifications_enabled, business_type (business/organisation/other), business_name, business_address, other_details`. Only sent fields change. |
| ✅🔑 | POST | `/me/photo` | multipart `image` (jpg/png/webp, at most 2 MB and 4000 px). Returns the user. |
| ✅🔑 | POST | `/me/business-logo` | same as photo |
| ✅🔑 | POST | `/me/devices` | `{fcm_token, platform (android/ios), app_version?}`. 204. Call after login and when the token changes. |
| ✅🔑 | GET | `/me/kundlis` | Saved birth profiles (max 20, newest first) |
| ✅🔑 | POST | `/me/kundlis` | `{name, relation?, gender, birth_date, birth_time, birth_place, lat, lng, tz_offset?}`. 201. |
| ✅🔑 | PUT | `/me/kundlis/{id}` | same fields |
| ✅🔑 | DELETE | `/me/kundlis/{id}` | 204 |
| ⏳🔑 | GET/POST/DELETE | `/me/favorites` | after the content modules |

User object:
```json
{"id":1,"mobile":"9876543210","name":"Raj","gender":"male","birth_date":"1995-04-12","birth_time":"06:30",
 "birth_place":"Surat","birth_lat":21.17,"birth_lng":72.83,"city":"Surat","rashi":4,"language":"hi",
 "notifications_enabled":true,"photo_url":"https://.../storage/profile-photos/x.jpg",
 "business_type":"business","business_name":"Raj Traders","business_address":"...","business_logo_url":null,"other_details":null}
```

## Astrology (provider to be connected)
Answers are cached for 30 days by input, so repeated questions do not call the provider again.
Until a provider is configured these return **503**, and the app should use its on-phone calculation.

| Status | Method | Path | Notes |
|---|---|---|---|
| ✅ | GET | `/astro/panchang?date=&lat=&lng=&tz=` | Places within about 1 km share one cached answer. |
| ✅🔑 | POST | `/astro/kundli` | `{date, time, lat, lng, tz?}` |
| ✅🔑 | POST | `/astro/matching` | `{boy:{date,time,lat,lng,tz?}, girl:{...}}` |

The `data` shape of these three is set when the provider is chosen.

## Posters and Mantras
Text fields come in the `Accept-Language` language (en/hi/gu), falling back to English.

| Status | Method | Path | Notes |
|---|---|---|---|
| ✅ | GET | `/posters/categories` | `[{id, name, icon_url}]`, cached |
| ✅ | GET | `/posters?category=&page=` | 30 per page: `{id, category_id, title, image_url, frame}`. Hidden: turned off, or `show_from` in the future. |
| ✅ | GET | `/mantras/categories` | same shape as poster categories |
| ✅ | GET | `/mantras?category=&page=` | 30 per page: `{id, category_id, title, deity, image_url, audio_url, duration_seconds}` |
| ✅ | GET | `/mantras/{id}` | the same plus `lyrics` and `meaning` |

Admin panel (`/admin`): categories (poster/mantra), poster upload, and mantra upload with image and audio.
The first admin is created with `php artisan admin:create <email> "<name>"`.

## Festivals and Rashifal
| Status | Method | Path | Notes |
|---|---|---|---|
| ✅ | GET | `/festivals?month=&year=&type=festival\|vrat` | One month (default: this month), up to 100: `{id, name, tithi, date, is_vrat, image_url}` |
| ✅ | GET | `/festivals/upcoming?limit=5` | From today, for Home (limit 1–20) |
| ✅ | GET | `/festivals/{id}` | The same plus `about` and `rituals` (array of steps) |
| ✅ | GET | `/rashifal?date=&rashi=` | Default today; all written rashis, or one with `rashi` (0–11). Each: `{rashi, date, texts:{overall,love,career,health,money}, stars:{area: 1–5}, lucky_number, lucky_colour, lucky_time, lucky_direction}`. A rashi the admin has not written is missing, so the app should show its built-in text. |

## Content (planned, managed from the admin panel)
All lists paginated. Text comes in the user's language (`Accept-Language: hi`), falling back to English.

| Module | Endpoints |
|---|---|
| Muhurat | `GET /muhurat/categories`, `GET /muhurat/{category}?year=` |
| Temples & Darshan | `GET /temples?state=&type=`, `GET /temples/{id}`, `GET /darshan/today` |
| Katha, Gita, Prayer | `GET /library/categories`, `GET /library?category=`, `GET /library/{id}`, `GET /gita/chapters`, `GET /gita/chapters/{n}/verses`, `GET /suvichar?date=` |
| Studio | `GET /videos?category=`, `GET /wallpapers?category=` |
| Guides | `GET /guides/{module}`, `GET /guides/{module}/{id}` (gems, rudraksha, tarot, numerology, palmistry, graphology) |
| Live & Learning | `GET /live`, `GET /courses`, `GET /courses/{id}` |
| Notifications 🔑 | `GET /notifications`, `POST /notifications/read` |
| Feedback | `POST /feedback` |
