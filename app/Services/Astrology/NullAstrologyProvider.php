<?php

namespace App\Services\Astrology;

/** Used until the provider's details are given. */
class NullAstrologyProvider implements AstrologyProvider
{
    public function panchang(string $date, float $lat, float $lng, float $tz): array
    {
        throw new AstrologyUnavailable('No astrology provider configured.');
    }

    public function kundli(array $birth): array
    {
        throw new AstrologyUnavailable('No astrology provider configured.');
    }

    public function matching(array $boy, array $girl): array
    {
        throw new AstrologyUnavailable('No astrology provider configured.');
    }
}
