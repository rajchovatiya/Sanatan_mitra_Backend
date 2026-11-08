<?php

namespace App\Services\Astrology;

/**
 * An external Panchang / Kundli provider. Each provider gets its own class;
 * the app only sees our API, so the provider can change without an app update.
 *
 * Birth details are arrays with: date (Y-m-d), time (H:i), lat, lng, tz.
 *
 * @throws AstrologyUnavailable when the provider is not set up or does not answer.
 */
interface AstrologyProvider
{
    public function panchang(string $date, float $lat, float $lng, float $tz): array;

    public function kundli(array $birth): array;

    public function matching(array $boy, array $girl): array;
}
