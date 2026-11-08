<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BirthDetailsRules;
use App\Http\Requests\Api\KundliChartRequest;
use App\Http\Requests\Api\MatchingRequest;
use App\Http\Requests\Api\PanchangRequest;
use App\Services\Astrology\AstrologyService;
use Illuminate\Http\JsonResponse;

/** Answers 503 until a provider is configured; the app then uses its own calculation. */
class AstrologyController extends Controller
{
    public function __construct(private AstrologyService $astrology) {}

    public function panchang(PanchangRequest $request): JsonResponse
    {
        $q = $request->validated();

        return response()->json(['data' => $this->astrology->panchang(
            $q['date'], (float) $q['lat'], (float) $q['lng'], (float) ($q['tz'] ?? 5.5),
        )]);
    }

    public function kundli(KundliChartRequest $request): JsonResponse
    {
        return response()->json(['data' => $this->astrology->kundli(BirthDetailsRules::extract($request->validated()))]);
    }

    public function matching(MatchingRequest $request): JsonResponse
    {
        $q = $request->validated();

        return response()->json(['data' => $this->astrology->matching(
            BirthDetailsRules::extract($q['boy']),
            BirthDetailsRules::extract($q['girl']),
        )]);
    }
}
