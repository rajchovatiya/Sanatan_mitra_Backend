<?php

namespace App\Services\Astrology;

use Illuminate\Support\Facades\Cache;

/**
 * Calls the provider and caches each answer: the same date and place (or the
 * same birth details) always give the same result, so 1000 users asking for
 * today's Panchang in one city cost one provider call.
 */
class AstrologyService
{
    public function __construct(private AstrologyProvider $provider) {}

    public function panchang(string $date, float $lat, float $lng, float $tz): array
    {
        // About 1 km of rounding: same sunrise and Panchang, far fewer cache entries.
        $lat = round($lat, 2);
        $lng = round($lng, 2);

        return $this->remember(
            "astro:v2:panchang:{$date}:{$lat}:{$lng}:{$tz}",
            fn () => $this->provider->panchang($date, $lat, $lng, $tz),
        );
    }

    public function kundli(array $birth): array
    {
        return $this->remember(
            'astro:v2:kundli:'.$this->fingerprint($birth),
            fn () => $this->provider->kundli($birth),
        );
    }

    public function matching(array $boy, array $girl): array
    {
        return $this->remember(
            'astro:v2:match:'.$this->fingerprint($boy).':'.$this->fingerprint($girl),
            fn () => $this->provider->matching($boy, $girl),
        );
    }

    /** Failed calls throw, so they are never cached. */
    private function remember(string $key, callable $fetch): array
    {
        return Cache::remember($key, now()->addDays(config('sanatan.astrology.cache_days')), $fetch);
    }

    private function fingerprint(array $birth): string
    {
        return sha1(implode('|', [
            $birth['date'], $birth['time'], round($birth['lat'], 4), round($birth['lng'], 4), $birth['tz'],
        ]));
    }
}
