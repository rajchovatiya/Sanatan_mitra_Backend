<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

/** Up to 10 posters at once into one category; each gets an English title. */
class PosterBulkRequest extends ContentRequest
{
    public const MAX_FILES = 10;

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
            'images' => ['required', 'array', 'min:1', 'max:'.self::MAX_FILES],
            'images.*' => $this->imageRules(true),
            'titles' => ['nullable', 'array'],
            'titles.*' => ['nullable', 'string', 'max:100'],
            'footer_below' => ['boolean'],
            'show_date' => ['boolean'],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'date_position' => ['nullable', Rule::in(array_keys(\App\Models\Poster::DATE_POSITIONS))],
            ...$this->common(),
        ];
    }

    public function messages(): array
    {
        return ['images.max' => 'Upload at most '.self::MAX_FILES.' posters at a time.'];
    }
}
