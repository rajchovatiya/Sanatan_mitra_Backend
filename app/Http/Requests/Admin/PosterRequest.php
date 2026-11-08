<?php

namespace App\Http\Requests\Admin;

use App\Models\Poster;
use Illuminate\Validation\Rule;

class PosterRequest extends ContentRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', 'poster')],
            ...TranslatedRules::for('title', max: 100),
            'image' => $this->imageRules($this->isCreate()),
            'frame' => ['required', Rule::in(Poster::FRAMES)],
            'show_from' => ['nullable', 'date_format:Y-m-d'],
            ...$this->common(),
        ];
    }
}
