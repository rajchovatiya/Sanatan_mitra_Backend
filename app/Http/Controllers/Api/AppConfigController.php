<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AppConfig;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{
    /** Called on app start: versions, maintenance mode and feature switches. */
    public function __invoke(): JsonResponse
    {
        return response()->json(['data' => AppConfig::all()]);
    }
}
