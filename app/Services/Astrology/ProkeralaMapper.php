<?php

namespace App\Services\Astrology;

/**
 * Turns Prokerala answers into the app's own compact format, so the app never depends on
 * one provider's shape. Indexes follow the app's lists: rashi 0 = Mesha, nakshatra 0 = Ashwini,
 * yoga 0 = Vishkambha, tithi 0 = Shukla Pratipada … 14 = Purnima, 15 = Krishna Pratipada … 29 = Amavasya.
 * Names (planets, gana, nadi, yoni, varna, vashya, karana, gem) use the app's English keys,
 * which the app translates.
 */
final class ProkeralaMapper
{
    /** Prokerala planet id => app name and chart mark. */
    private const PLANETS = [
        100 => ['Lagna', 'Asc'], 0 => ['Surya', 'Su'], 1 => ['Chandra', 'Mo'], 4 => ['Mangal', 'Ma'],
        2 => ['Budh', 'Me'], 5 => ['Guru', 'Ju'], 3 => ['Shukra', 'Ve'], 6 => ['Shani', 'Sa'],
        101 => ['Rahu', 'Ra'], 102 => ['Ketu', 'Ke'],
    ];

    private const KARANAS = ['Bava', 'Balava', 'Kaulava', 'Taitila', 'Garaja', 'Vanija', 'Vishti', 'Shakuni', 'Chatushpada', 'Naga', 'Kimstughna'];

    private const GEMS = [
        'Surya' => 'Ruby', 'Chandra' => 'Pearl', 'Mangal' => 'Red Coral', 'Budh' => 'Emerald',
        'Guru' => 'Yellow Sapphire', 'Shukra' => 'Diamond', 'Shani' => 'Blue Sapphire',
    ];

    // ---------------- Panchang ----------------

    public static function panchang(array $p): array
    {
        $periods = [];
        foreach ([...($p['auspicious_period'] ?? []), ...($p['inauspicious_period'] ?? [])] as $period) {
            $key = match ($period['name']) {
                'Abhijit Muhurat' => 'abhijit',
                'Brahma Muhurat' => 'brahma',
                'Amrit Kaal' => 'amrit_kaal',
                'Rahu' => 'rahu_kaal',
                'Yamaganda' => 'yamaganda',
                'Gulika' => 'gulika',
                'Dur Muhurat' => 'durmuhurat',
                'Varjyam' => 'varjyam',
                default => null,
            };
            if ($key) {
                $periods[$key] = array_map(fn ($s) => ['start' => $s['start'], 'end' => $s['end']], $period['period']);
            }
        }

        return [
            'source' => 'prokerala',
            'sunrise' => $p['sunrise'] ?? null,
            'sunset' => $p['sunset'] ?? null,
            'moonrise' => $p['moonrise'] ?? null,
            'moonset' => $p['moonset'] ?? null,
            'tithi' => array_map(fn ($t) => ['index' => self::tithiIndex($t), 'end' => $t['end']], $p['tithi'] ?? []),
            'nakshatra' => array_map(fn ($n) => ['index' => (int) $n['id'], 'end' => $n['end']], $p['nakshatra'] ?? []),
            'yoga' => array_map(fn ($y) => ['index' => (int) $y['id'], 'end' => $y['end']], $p['yoga'] ?? []),
            'karana' => array_map(fn ($k) => ['name' => self::KARANAS[(int) $k['id']] ?? $k['name'], 'end' => $k['end']], $p['karana'] ?? []),
            'periods' => $periods,
        ];
    }

    /** Prokerala numbers Krishna Pratipada … Amavasya as 1-15 and Shukla Pratipada … Purnima as 16-30. */
    private static function tithiIndex(array $t): int
    {
        $id = (int) $t['id'];

        return $id >= 16 ? $id - 16 : 15 + $id - 1;
    }

    // ---------------- Kundli ----------------

    public static function kundli(array $k, array $positions): array
    {
        $info = $k['nakshatra_details'] ?? [];
        $extra = $info['additional_info'] ?? [];
        $moonRashi = (int) ($info['chandra_rasi']['id'] ?? 0);

        $planets = [];
        $lagna = 0;
        $moonDegree = 0.0;
        foreach ($positions['planet_position'] ?? [] as $pl) {
            [$name, $short] = self::PLANETS[(int) $pl['id']] ?? [$pl['name'], $pl['name']];
            $rashi = (int) $pl['rasi']['id'];
            if ($name === 'Lagna') {
                $lagna = $rashi;
            }
            if ($name === 'Chandra') {
                $moonDegree = (float) $pl['degree'];
            }
            $planets[] = [
                'name' => $name,
                'short' => $short,
                'rashi' => $rashi,
                'longitude' => round((float) $pl['longitude'], 4),
                'degree' => self::dms((float) $pl['degree']),
                'nakshatra' => (int) floor(((float) $pl['longitude']) / (360 / 27)) % 27,
                'retro' => (bool) ($pl['is_retrograde'] ?? false),
            ];
        }
        // House counted from the Lagna rashi (whole-sign houses, as in the North Indian chart).
        foreach ($planets as &$pl) {
            $pl['house'] = ($pl['rashi'] - $lagna + 12) % 12 + 1;
        }
        unset($pl);

        $rashiLord = self::PLANETS[(int) ($info['chandra_rasi']['lord']['id'] ?? -1)][0] ?? null;

        return [
            'source' => 'prokerala',
            'lagna_rashi' => $lagna,
            'moon_rashi' => $moonRashi,
            'sun_rashi' => (int) ($info['soorya_rasi']['id'] ?? 0),
            'nakshatra' => ['index' => (int) ($info['nakshatra']['id'] ?? 0), 'pada' => (int) ($info['nakshatra']['pada'] ?? 1)],
            'varna' => self::varna($moonRashi),
            'vashya' => self::vashya($moonRashi, $moonDegree),
            'yoni' => self::yoni($extra['animal_sign'] ?? ''),
            'gana' => match (strtolower($extra['ganam'] ?? '')) {
                'deva' => 'Deva',
                'manushya', 'manava' => 'Manushya',
                default => 'Rakshasa',
            },
            'nadi' => match (strtolower($extra['nadi'] ?? '')) {
                'vata', 'aadi', 'adi' => 'Adi',
                'pitta', 'madhya' => 'Madhya',
                default => 'Antya',
            },
            'rashi_lord' => $rashiLord,
            'lucky_gem' => self::GEMS[$rashiLord] ?? null,
            'syllables' => $extra['syllables'] ?? null,
            'manglik' => [
                'has' => (bool) ($k['mangal_dosha']['has_dosha'] ?? false),
                'description' => $k['mangal_dosha']['description'] ?? null,
            ],
            'kaal_sarp' => self::kaalSarp($planets),
            'yogas' => self::yogas($k['yoga_details'] ?? []),
            'dasha' => array_map(fn ($d) => [
                'planet' => self::PLANETS[(int) $d['id']][0] ?? $d['name'],
                'start' => substr($d['start'], 0, 10),
                'end' => substr($d['end'], 0, 10),
                // Sub-periods only; the deeper levels make the answer 20 times bigger.
                'antardasha' => array_map(fn ($a) => [
                    'planet' => self::PLANETS[(int) $a['id']][0] ?? $a['name'],
                    'start' => substr($a['start'], 0, 10),
                    'end' => substr($a['end'], 0, 10),
                ], $d['antardasha'] ?? []),
            ], $k['dasha_periods'] ?? []),
            'planets' => $planets,
        ];
    }

    /** By Moon rashi: water signs Brahmin, fire Kshatriya, earth Vaishya, air Shudra. */
    private static function varna(int $rashi): string
    {
        return ['Kshatriya', 'Vaishya', 'Shudra', 'Brahmin'][$rashi % 4];
    }

    private static function vashya(int $rashi, float $degree): string
    {
        return match ($rashi) {
            0, 1 => 'Chatushpada',
            2, 5, 6, 10 => 'Manav',
            3, 11 => 'Jalchar',
            4 => 'Vanchar',
            7 => 'Keet',
            8 => $degree < 15 ? 'Manav' : 'Chatushpada',
            9 => $degree < 15 ? 'Chatushpada' : 'Jalchar',
            default => 'Manav',
        };
    }

    private static function yoni(string $animal): string
    {
        return match (strtolower($animal)) {
            'horse' => 'Ashwa (Horse)',
            'elephant' => 'Gaja (Elephant)',
            'sheep', 'goat', 'ram' => 'Mesha (Ram)',
            'serpent', 'snake' => 'Sarpa (Serpent)',
            'dog' => 'Shwan (Dog)',
            'cat' => 'Marjar (Cat)',
            'rat' => 'Mushak (Rat)',
            'cow' => 'Gau (Cow)',
            'buffalo' => 'Mahish (Buffalo)',
            'tiger' => 'Vyaghra (Tiger)',
            'deer', 'hare' => 'Mriga (Deer)',
            'monkey' => 'Vanar (Monkey)',
            'mongoose' => 'Nakul (Mongoose)',
            'lion' => 'Simha (Lion)',
            default => $animal,
        };
    }

    /** Kaal Sarp: all seven planets on one side of the Rahu–Ketu axis. */
    private static function kaalSarp(array $planets): bool
    {
        $rahu = collect($planets)->firstWhere('name', 'Rahu')['longitude'] ?? null;
        if ($rahu === null) {
            return false;
        }
        $sides = collect($planets)
            ->whereIn('name', ['Surya', 'Chandra', 'Mangal', 'Budh', 'Guru', 'Shukra', 'Shani'])
            ->map(fn ($p) => fmod($p['longitude'] - $rahu + 360, 360) < 180)
            ->unique();

        return $sides->count() === 1;
    }

    private static function yogas(array $groups): array
    {
        $found = [];
        foreach ($groups as $group) {
            foreach ($group['yoga_list'] ?? [] as $y) {
                if ($y['has_yoga'] ?? false) {
                    $found[] = ['name' => $y['name'], 'description' => trim($y['description'] ?? '')];
                }
            }
        }

        return $found;
    }

    private static function dms(float $degree): string
    {
        $whole = (int) floor($degree);

        return sprintf("%d°%02d'", $whole, (int) floor(($degree - $whole) * 60));
    }

    // ---------------- Matching ----------------

    public static function matching(array $m): array
    {
        $names = [1 => 'Varna', 2 => 'Vashya', 3 => 'Tara', 4 => 'Yoni', 5 => 'Graha Maitri', 6 => 'Gana', 7 => 'Bhakoot', 8 => 'Nadi'];
        $person = fn (array $info, array $dosha) => [
            'rashi' => (int) ($info['rasi']['id'] ?? 0),
            'nakshatra' => (int) ($info['nakshatra']['id'] ?? 0),
            'pada' => (int) ($info['nakshatra']['pada'] ?? 1),
            'manglik' => (bool) ($dosha['has_dosha'] ?? false),
        ];

        return [
            'source' => 'prokerala',
            'total' => (float) ($m['guna_milan']['total_points'] ?? 0),
            'max' => (int) ($m['guna_milan']['maximum_points'] ?? 36),
            'kootas' => array_map(fn ($g) => [
                'name' => $names[(int) $g['id']] ?? $g['name'],
                'obtained' => (float) $g['obtained_points'],
                'max' => (int) $g['maximum_points'],
            ], $m['guna_milan']['guna'] ?? []),
            'boy' => $person($m['boy_info'] ?? [], $m['boy_mangal_dosha_details'] ?? []),
            'girl' => $person($m['girl_info'] ?? [], $m['girl_mangal_dosha_details'] ?? []),
            'provider_verdict' => $m['message']['type'] ?? null,
        ];
    }
}
