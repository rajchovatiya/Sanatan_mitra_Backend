<?php

namespace App\Http\Requests\Admin;

class SuvicharRequest extends ContentRequest
{
    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date_format:Y-m-d'],
            ...TranslatedRules::for('text', max: 500),
            ...TranslatedRules::for('author', required: false, max: 80),
            'is_active' => ['boolean'],
        ];
    }
}
