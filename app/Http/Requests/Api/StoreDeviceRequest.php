<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'fcm_token' => ['required', 'string', 'max:255'],
            'platform' => ['required', 'in:android,ios'],
            'app_version' => ['nullable', 'string', 'max:20'],
        ];
    }
}
