<?php

namespace Tests\Fakes;

use App\Services\Astrology\AstrologyProvider;

class FakeAstrologyProvider implements AstrologyProvider
{
    public int $calls = 0;

    public function panchang(string $date, float $lat, float $lng, float $tz): array
    {
        $this->calls++;

        return ['date' => $date, 'tithi' => 'Dwadashi'];
    }

    public function kundli(array $birth): array
    {
        $this->calls++;

        return ['lagna' => 'Tula'];
    }

    public function matching(array $boy, array $girl): array
    {
        $this->calls++;

        return ['guna' => 28];
    }
}
