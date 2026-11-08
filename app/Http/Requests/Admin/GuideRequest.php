<?php

namespace App\Http\Requests\Admin;

use App\Models\Guide;
use Illuminate\Validation\Rule;

class GuideRequest extends ContentRequest
{
    public function rules(): array
    {
        return [
            'module' => ['required', Rule::in(array_keys(Guide::MODULES))],
            ...TranslatedRules::for('title', max: 120),
            ...TranslatedRules::for('subtitle', required: false, max: 150),
            ...TranslatedRules::for('body', required: false, max: 0),
            ...TranslatedRules::for('facts', required: false, max: 0),
            ...TranslatedRules::for('points', required: false, max: 0),
            'image' => $this->imageRules(false),
            ...$this->common(),
        ];
    }
}
