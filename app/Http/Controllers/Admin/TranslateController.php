<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Translation\Translator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Used by the "Translate" button on admin forms; fills the Hindi and Gujarati boxes. */
class TranslateController extends Controller
{
    public function __invoke(Request $request, Translator $translator): JsonResponse
    {
        $texts = $request->validate([
            'texts' => ['required', 'array', 'max:20'],
            'texts.*' => ['required', 'string', 'max:5000'],
        ])['texts'];

        $fields = array_keys($texts);
        $translations = [];
        foreach (['hi', 'gu'] as $lang) {
            $translations[$lang] = array_combine($fields, $translator->translate(array_values($texts), $lang));
        }

        return response()->json(['translations' => $translations]);
    }
}
