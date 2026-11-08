<?php

return [

    /*
    | Login OTP. "log" writes the code to storage/logs (development only).
    | A real SMS driver is added before launch.
    */
    'otp' => [
        'driver' => env('OTP_DRIVER', 'log'),
        'length' => 6,
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
        // Results for the same input never change, so they are cached to save provider calls.
        'cache_days' => 30,
    ],

    'kundlis_per_user' => 20,

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
        'features' => [
            'panchang' => true, 'rashifal' => true, 'kundli' => true, 'matching' => true,
            'muhurat' => true, 'gems' => true, 'tarot' => true, 'numerology' => true,
            'palmistry' => true, 'graphology' => true, 'studio' => true, 'video' => true,
            'mantra' => true, 'temples' => true, 'festivals' => true, 'library' => true,
            'learning' => true,
        ],
    ],
];
