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

    /** The nearest town or village to a GPS position (for filling an address). */
    public function nearest(Request $request): JsonResponse
    {
        $q = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);
        $lat = (float) $q['lat'];
        $lng = (float) $q['lng'];

        // About 11 km box first (uses the lat index), then the closest by plain distance.
        $scale = cos(deg2rad($lat)) ** 2;
        $best = null;
        foreach ([0.1, 0.5] as $d) {
            $best = Place::whereBetween('lat', [$lat - $d, $lat + $d])
                ->whereBetween('lng', [$lng - $d, $lng + $d])
                ->get()
                ->sortBy(fn (Place $p) => ($p->lat - $lat) ** 2 + (($p->lng - $lng) ** 2) * $scale)
                ->first();
            if ($best) {
                break;
            }
        }

        return response()->json(['data' => $best?->toApi()]);
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
