<?php

namespace App\Http\Requests\Admin;

use App\Models\Category;
use Illuminate\Validation\Rule;

class CategoryRequest extends ContentRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(Category::TYPES))],
            ...TranslatedRules::for('name', max: 60),
            'icon' => $this->imageRules(false),
            ...$this->common(),
        ];
    }
}
