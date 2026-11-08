<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class MuhuratDateRequest extends ContentRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', 'muhurat')],
            'date' => ['required', 'date_format:Y-m-d'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            ...TranslatedRules::for('note', required: false, max: 300),
            'is_active' => ['boolean'],
        ];
    }
}
