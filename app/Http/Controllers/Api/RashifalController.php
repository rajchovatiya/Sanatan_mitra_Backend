<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RashifalResource;
use App\Models\Rashifal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RashifalController extends Controller
{
    /**
     * All 12 rashis for a day (today by default), or one with ?rashi=.
     * The whole day is cached per language, so every user's request is one cache read.
     * A rashi the admin has not written yet is simply missing; the app shows its own text then.
     */
    public function index(Request $request): JsonResponse
    {
        $q = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
            'rashi' => ['nullable', 'integer', 'between:0,11'],
        ]);
        $date = $q['date'] ?? today()->toDateString();

        $day = Cache::remember(Rashifal::cacheKey($date, app()->getLocale()), now()->addDay(), fn () => RashifalResource::collection(
            Rashifal::whereDate('date', $date)->orderBy('rashi')->get()
        )->resolve($request));

        if (isset($q['rashi'])) {
            $day = array_values(array_filter($day, fn ($r) => $r['rashi'] === (int) $q['rashi']));
        }

        return response()->json(['data' => $day]);
    }
}
