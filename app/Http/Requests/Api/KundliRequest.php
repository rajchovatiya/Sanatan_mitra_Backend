<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/** A saved birth profile (create and update use the same full set of fields). */
class KundliRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:80'],
            'relation' => ['nullable', 'string', 'max:30'],
            'gender' => ['required', 'in:male,female,other'],
            'birth_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:today', 'after:1900-01-01'],
            'birth_time' => ['required', 'date_format:H:i'],
            'birth_place' => ['required', 'string', 'max:120'],
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
            'tz_offset' => ['nullable', 'numeric', 'between:-12,14'],
        ];
    }
}
