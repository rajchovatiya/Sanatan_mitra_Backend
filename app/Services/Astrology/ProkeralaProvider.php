<?php

namespace App\Services\Astrology;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Prokerala Astrology API v2 (OAuth client credentials). Lahiri ayanamsa.
 * Answers are returned as Prokerala sends them ("data"); AstrologyService caches them.
 */
class ProkeralaProvider implements AstrologyProvider
{
    private const BASE = 'https://api.prokerala.com';

    private const LAHIRI = 1;

    public function __construct(private string $clientId, private string $clientSecret) {}

    public function panchang(string $date, float $lat, float $lng, float $tz): array
    {
        return ProkeralaMapper::panchang($this->get('/v2/astrology/panchang/advanced', [
            'ayanamsa' => self::LAHIRI,
            'coordinates' => $this->coordinates($lat, $lng),
            // Around sunrise, so the day's tithi and nakshatra are the ones in force that morning.
            'datetime' => $this->datetime($date, '06:00', $tz),
            'la' => 'en',
        ]));
    }

    /** Two calls: the Kundli details and the planet positions (for the Lagna chart). */
    public function kundli(array $birth): array
    {
        $query = [
            'ayanamsa' => self::LAHIRI,
            'coordinates' => $this->coordinates($birth['lat'], $birth['lng']),
            'datetime' => $this->datetime($birth['date'], $birth['time'], $birth['tz']),
            'la' => 'en',
        ];

        // The full Kundli (with every dasha level) is about 250 KB, so it gets more time.
        return ProkeralaMapper::kundli(
            $this->get('/v2/astrology/kundli/advanced', timeout: 60, query: $query),
            $this->get('/v2/astrology/planet-position', $query),
        );
    }

    public function matching(array $boy, array $girl): array
    {
        return ProkeralaMapper::matching($this->get('/v2/astrology/kundli-matching/advanced', [
            'ayanamsa' => self::LAHIRI,
            'boy_coordinates' => $this->coordinates($boy['lat'], $boy['lng']),
            'boy_dob' => $this->datetime($boy['date'], $boy['time'], $boy['tz']),
            'girl_coordinates' => $this->coordinates($girl['lat'], $girl['lng']),
            'girl_dob' => $this->datetime($girl['date'], $girl['time'], $girl['tz']),
            'la' => 'en',
        ]));
    }

    private function get(string $path, array $query, int $timeout = 20): array
    {
        try {
            $data = Http::withToken($this->token())
                ->timeout($timeout)
                ->get(self::BASE.$path, $query)
                ->throw()
                ->json('data');
        } catch (RequestException|ConnectionException $e) {
            // Logged without the token; the app falls back to its own calculation.
            Log::warning('Prokerala request failed', ['path' => $path, 'error' => $e->getMessage()]);
            throw new AstrologyUnavailable('Astrology provider request failed.');
        }

        return is_array($data) ? $data : throw new AstrologyUnavailable('Astrology provider sent no data.');
    }

    /** Access token, cached until shortly before it expires (about an hour). */
    private function token(): string
    {
        $key = 'prokerala:token:'.md5($this->clientId);
        if ($token = Cache::get($key)) {
            return $token;
        }

        try {
            $res = Http::asForm()->timeout(15)->post(self::BASE.'/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ])->throw()->json();
        } catch (RequestException|ConnectionException) {
            throw new AstrologyUnavailable('Astrology provider login failed. Check PROKERALA_CLIENT_ID and PROKERALA_CLIENT_SECRET.');
        }

        Cache::put($key, $res['access_token'], now()->addSeconds(max(60, ($res['expires_in'] ?? 3600) - 120)));

        return $res['access_token'];
    }

    private function coordinates(float $lat, float $lng): string
    {
        return round($lat, 4).','.round($lng, 4);
    }

    /** ISO 8601 with the place's UTC offset, e.g. 2026-11-12T06:00:00+05:30. */
    private function datetime(string $date, string $time, float $tz): string
    {
        $minutes = (int) round(abs($tz) * 60);

        return sprintf('%sT%s:00%s%02d:%02d', $date, $time, $tz < 0 ? '-' : '+', intdiv($minutes, 60), $minutes % 60);
    }
}
