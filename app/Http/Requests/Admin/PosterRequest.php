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
        $this->merge(['footer_below' => $this->boolean('footer_below'), 'show_date' => $this->boolean('show_date')]);
        // Ticked days as whole numbers; none ticked = no particular day.
        $days = collect((array) $this->input('weekdays', []))->map(fn ($d) => (int) $d)->filter()->unique()->sort()->values()->all();
        $this->merge(['weekdays' => $days ?: null]);
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', 'poster')],
            ...TranslatedRules::for('title', max: 100),
            'image' => $this->imageRules($this->isCreate()),
            'frame' => ['required', Rule::in(Poster::FRAMES)],
            'footer_below' => ['boolean'],
            'show_date' => ['boolean'],
            'date_position' => ['nullable', Rule::in(array_keys(Poster::DATE_POSITIONS))],
            'date_on' => ['nullable', 'date_format:Y-m-d'],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'show_from' => ['nullable', 'date_format:Y-m-d'],
            'today_on' => ['nullable', 'date_format:Y-m-d'],
            ...$this->common(),
        ];
    }
}
