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
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $saved = Setting::query()->pluck('value', 'key')->all();

            return array_replace(config('sanatan.app'), $saved);
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
