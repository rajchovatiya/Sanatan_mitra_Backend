<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class MatchingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'boy' => ['required', 'array'],
            'girl' => ['required', 'array'],
            ...BirthDetailsRules::for('boy.'),
            ...BirthDetailsRules::for('girl.'),
        ];
    }
}
