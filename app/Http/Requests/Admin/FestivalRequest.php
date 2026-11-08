<?php

namespace App\Http\Requests\Admin;

class FestivalRequest extends ContentRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->merge(['is_vrat' => $this->boolean('is_vrat')]);
    }

    public function rules(): array
    {
        return [
            'is_vrat' => ['boolean'],
            ...TranslatedRules::for('name', max: 120),
            ...TranslatedRules::for('tithi', required: false, max: 80),
            ...TranslatedRules::for('about', required: false, max: 0),
            ...TranslatedRules::for('rituals', required: false, max: 0),
            'date' => ['required', 'date_format:Y-m-d'],
            'image' => $this->imageRules(false),
            ...$this->common(),
        ];
    }
}
