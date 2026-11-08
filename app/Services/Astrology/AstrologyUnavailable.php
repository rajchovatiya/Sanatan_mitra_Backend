<?php

namespace App\Services\Astrology;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/** The provider is not configured or failed. The app falls back to its on-phone calculation. */
class AstrologyUnavailable extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json(['message' => 'Astrology service is not available right now.'], 503);
    }
}
