<?php

namespace App\Http\Requests\Admin;

use App\Models\Temple;
use Illuminate\Validation\Rule;

class TempleRequest extends ContentRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(Temple::TYPES))],
            ...TranslatedRules::for('name', max: 120),
            ...TranslatedRules::for('location', required: false, max: 150),
            'state' => ['nullable', 'string', 'max:60'],
            ...TranslatedRules::for('deity', required: false, max: 100),
            ...TranslatedRules::for('about', required: false, max: 0),
            ...TranslatedRules::for('timings', required: false, max: 150),
            ...TranslatedRules::for('best_time', required: false, max: 100),
            'lat' => ['nullable', 'numeric', 'between:-90,90', 'required_with:lng'],
            'lng' => ['nullable', 'numeric', 'between:-180,180', 'required_with:lat'],
            'image' => $this->imageRules(false),
            ...$this->common(),
        ];
    }
}
