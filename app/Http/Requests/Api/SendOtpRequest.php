<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Indian mobile number, 10 digits without +91.
            'mobile' => ['required', 'string', 'regex:/^[6-9]\d{9}$/'],
        ];
    }

    public function messages(): array
    {
        return ['mobile.regex' => 'Enter a valid 10-digit mobile number.'];
    }
}
