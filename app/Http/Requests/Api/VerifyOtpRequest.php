<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
            'code' => ['required', 'string', 'digits:'.config('sanatan.otp.length')],
            'device_name' => ['nullable', 'string', 'max:60'],
        ];
    }
}
