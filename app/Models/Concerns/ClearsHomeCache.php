<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Cache;

/**
 * The app's Home (GET /home) is cached for 10 minutes per day and language.
 * Models shown there use this, so a change in the admin panel shows at once.
 */
trait ClearsHomeCache
{
    public static function bootClearsHomeCache(): void
    {
        $clear = static function (): void {
            foreach ([today()->subDay(), today(), today()->addDay()] as $day) {
                foreach (['en', 'hi', 'gu'] as $lang) {
                    Cache::forget('home:'.$day->toDateString().':'.$lang);
                }
            }
        };
        static::saved($clear);
        static::deleted($clear);
    }
}
