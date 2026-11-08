<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

/** Partial update: only the fields sent are changed. */
class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'nullable', 'string', 'max:80'],
            'gender' => ['sometimes', 'nullable', 'in:male,female,other'],
            'birth_date' => ['sometimes', 'nullable', 'date_format:Y-m-d', 'before_or_equal:today', 'after:1900-01-01'],
            'birth_time' => ['sometimes', 'nullable', 'date_format:H:i'],
            'birth_place' => ['sometimes', 'nullable', 'string', 'max:120'],
            'birth_lat' => ['sometimes', 'nullable', 'numeric', 'between:-90,90', 'required_with:birth_lng'],
            'birth_lng' => ['sometimes', 'nullable', 'numeric', 'between:-180,180', 'required_with:birth_lat'],
            'city' => ['sometimes', 'nullable', 'string', 'max:80'],
            'rashi' => ['sometimes', 'nullable', 'integer', 'between:0,11'],
            'language' => ['sometimes', 'in:en,hi,gu'],
            'notifications_enabled' => ['sometimes', 'boolean'],
            'business_type' => ['sometimes', 'nullable', 'in:business,organisation,other'],
            'business_name' => ['sometimes', 'nullable', 'string', 'max:100'],
            'business_address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'other_details' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
