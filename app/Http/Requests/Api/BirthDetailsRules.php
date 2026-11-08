<?php

namespace App\Http\Requests\Api;

/** Validation rules for one person's birth details, optionally nested under a key. */
final class BirthDetailsRules
{
    public static function for(string $prefix = ''): array
    {
        return [
            $prefix.'date' => ['required', 'date_format:Y-m-d', 'after:1900-01-01', 'before_or_equal:today'],
            $prefix.'time' => ['required', 'date_format:H:i'],
            $prefix.'lat' => ['required', 'numeric', 'between:-90,90'],
            $prefix.'lng' => ['required', 'numeric', 'between:-180,180'],
            $prefix.'tz' => ['nullable', 'numeric', 'between:-12,14'],
        ];
    }

    /** Normalised birth details for the astrology provider. */
    public static function extract(array $input): array
    {
        return [
            'date' => $input['date'],
            'time' => $input['time'],
            'lat' => (float) $input['lat'],
            'lng' => (float) $input['lng'],
            'tz' => (float) ($input['tz'] ?? 5.5),
        ];
    }
}
