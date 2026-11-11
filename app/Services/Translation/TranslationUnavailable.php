<?php

namespace App\Services\Translation;

use Illuminate\Http\JsonResponse;
use RuntimeException;

class TranslationUnavailable extends RuntimeException
{
    public function render(): JsonResponse
    {
        return response()->json(['message' => $this->getMessage()], 503);
    }
}
