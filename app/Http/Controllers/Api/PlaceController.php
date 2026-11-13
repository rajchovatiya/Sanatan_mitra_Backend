<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Place;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Birth place search. Our own GeoNames table first (fast, no limits);
 * if it finds almost nothing, OpenStreetMap (India only, cached) fills the gap.
 */
class PlaceController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:80']]);
        $key = Place::searchKey($request->string('q'));
        if (mb_strlen($key) < 2) {
            return response()->json(['data' => []]);
        }

        $found = Place::where('search_name', 'like', addcslashes($key, '%_\\').'%')
            ->orderByDesc('population')
            ->limit(25)
            ->get()
            ->map->toApi()
            ->all();

        if (count($found) < 3 && mb_strlen($key) >= 3 && config('services.places.osm_fallback', true)) {
            $seen = array_map(fn ($p) => round($p['lat'], 2).','.round($p['lng'], 2), $found);
            foreach ($this->openStreetMap($key) as $p) {
                if (! in_array(round($p['lat'], 2).','.round($p['lng'], 2), $seen, true)) {
                    $found[] = $p;
                }
            }
        }

        return response()->json(['data' => $found]);
    }

    /** @return array<int, array<string, mixed>> */
    private function openStreetMap(string $key): array
    {
        return Cache::remember('places:osm:'.md5($key), now()->addDays(30), function () use ($key) {
            try {
                $res = Http::timeout(6)->connectTimeout(4)
                    ->withHeaders(['User-Agent' => config('services.places.user_agent', 'SanatanShaily/1.0 (birth place search)')])
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $key, 'countrycodes' => 'in', 'format' => 'jsonv2',
                        'addressdetails' => 1, 'limit' => 8, 'accept-language' => 'en',
                    ]);
                if (! $res->ok()) {
                    return [];
                }
            } catch (Throwable) {
                return [];
            }

            $out = [];
            foreach ($res->json() ?? [] as $r) {
                $a = $r['address'] ?? [];
                $name = $a['village'] ?? $a['town'] ?? $a['city'] ?? $a['hamlet'] ?? $a['suburb'] ?? ($r['name'] ?? null);
                if (! $name) {
                    continue;
                }
                $out[] = [
                    'id' => null,
                    'name' => $name,
                    'district' => $a['state_district'] ?? $a['county'] ?? null,
                    'state' => $a['state'] ?? null,
                    'country_code' => 'IN',
                    'lat' => round((float) $r['lat'], 6),
                    'lng' => round((float) $r['lon'], 6),
                    'timezone' => 'Asia/Kolkata',
                ];
            }

            return $out;
        });
    }
}
