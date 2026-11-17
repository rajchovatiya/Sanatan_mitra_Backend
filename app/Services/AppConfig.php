<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/** Values for GET /app/config: defaults from config/sanatan.php, overridden by the settings table. */
class AppConfig
{
    private const CACHE_KEY = 'app_config';

    public static function all(): array
    {
        // Only the saved values are cached; the defaults are added each time,
        // so a new default (e.g. poster_design) shows up without clearing the cache.
        $saved = Cache::rememberForever(self::CACHE_KEY, fn () => Setting::query()->pluck('value', 'key')->all());

        return array_replace(config('sanatan.app'), $saved);
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
