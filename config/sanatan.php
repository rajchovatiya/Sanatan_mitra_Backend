<?php

return [

    /*
    | Login OTP. "log" writes the code to storage/logs (development only).
    | A real SMS driver is added before launch.
    */
    'otp' => [
        'driver' => env('OTP_DRIVER', 'log'),
        'length' => (int) env('OTP_LENGTH', 6),
        // Client testing only: every number gets this code and no SMS is sent.
        // Remove (leave empty) before launch.
        'test_code' => env('OTP_TEST_CODE'),
        'ttl' => 300,
        'max_attempts' => 5,
        // Returns the code in the API response so the app can be tested without SMS.
        // Never honoured in production.
        'expose_code' => (bool) env('OTP_EXPOSE_CODE', false),
    ],

    /*
    | Panchang / Kundli provider. "none" until the provider details are given;
    | the app keeps using its on-phone Panchang meanwhile.
    */
    'astrology' => [
        'driver' => env('ASTROLOGY_DRIVER', 'none'),
        'prokerala' => [
            'client_id' => env('PROKERALA_CLIENT_ID'),
            'client_secret' => env('PROKERALA_CLIENT_SECRET'),
        ],
        // Results for the same input never change, so they are cached to save provider calls.
        'cache_days' => 30,
    ],

    /*
    | Push notifications. "log" until Firebase is set up; then "fcm" with the path to the
    | service-account JSON from Firebase console (keep it outside public/ and out of Git).
    */
    'push' => [
        'driver' => env('PUSH_DRIVER', 'log'),
        'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/private/firebase.json')),
    ],

    /*
    | "Translate" button in admin forms (English to Hindi/Gujarati). "none" until a service is chosen.
    */
    'translate' => [
        'driver' => env('TRANSLATE_DRIVER', 'none'),
        'google_key' => env('GOOGLE_TRANSLATE_KEY'),
    ],

    'kundlis_per_user' => 20,

    // Where uploaded images and audio go. "public" = this server; switch to an S3/R2 disk before launch.
    'media_disk' => env('MEDIA_DISK', 'public'),

    // Content languages. English is the fallback when a translation is empty.
    'languages' => ['en', 'hi', 'gu'],

    /*
    | Defaults for GET /app/config. Values saved in the settings table override these.
    */
    'app' => [
        'android_min_version' => '1.0.0',
        'ios_min_version' => '1.0.0',
        'latest_version' => '1.0.0',
        'maintenance' => false,
        'maintenance_message' => null,
        'support_email' => null,
        'support_whatsapp' => null,
        // Poster design every poster in the app starts with (users can still
        // tap "Change design"). One of the keys of 'poster_designs' below.
        'poster_design' => 'royal',
        'features' => [
            'panchang' => true, 'rashifal' => true, 'kundli' => true, 'matching' => true,
            'muhurat' => true, 'gems' => true, 'tarot' => true, 'numerology' => true,
            'palmistry' => true, 'graphology' => true, 'studio' => true, 'video' => true,
            'mantra' => true, 'temples' => true, 'festivals' => true, 'library' => true,
            'learning' => true,
        ],
    ],

    // Poster designs of the app (keys must match the app's PosterDesign names).
    'poster_designs' => [
        'royal' => 'Royal – ivory header with round logo, maroon & gold footer, big portrait (users can customise colours, font, sizes)',
        'brand' => 'Brand – white header with full logo, big photo on the left, big WhatsApp + social',
        'saral' => 'Saral – simple footer',
        'shringar' => 'Shringar – curved cream & gold header, maroon footer with double gold border and floral corners, round gold-framed photo',
    ],
];
