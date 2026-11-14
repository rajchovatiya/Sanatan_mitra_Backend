<?php

namespace App\Http\Requests\Admin;

use App\Models\Poster;
use Illuminate\Validation\Rule;

class PosterRequest extends ContentRequest
{
    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        // An unticked checkbox is not sent at all.
        $this->merge(['footer_below' => $this->boolean('footer_below')]);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', 'poster')],
            ...TranslatedRules::for('title', max: 100),
            'image' => $this->imageRules($this->isCreate()),
            'frame' => ['required', Rule::in(Poster::FRAMES)],
            'footer_below' => ['boolean'],
            'show_from' => ['nullable', 'date_format:Y-m-d'],
            ...$this->common(),
        ];
    }
}
