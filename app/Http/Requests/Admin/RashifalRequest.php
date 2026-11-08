<?php

namespace App\Http\Requests\Admin;

use App\Models\Rashifal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RashifalRequest extends FormRequest
{
    public function rules(): array
    {
        $areas = [];
        foreach (Rashifal::AREAS as $area) {
            $areas = [...$areas, ...TranslatedRules::for($area, required: $area === 'overall', max: 1000)];
        }

        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'rashi' => [
                'required', 'integer', 'between:0,11',
                // One entry per rashi per day.
                Rule::unique('rashifals')->where('date', $this->input('date'))->ignore($this->route('rashifal')),
            ],
            ...$areas,
            'stars' => ['nullable', 'array:'.implode(',', Rashifal::AREAS)],
            'stars.*' => ['nullable', 'integer', 'between:1,5'],
            'lucky_number' => ['nullable', 'integer', 'between:1,99'],
            ...TranslatedRules::for('lucky_colour', required: false, max: 30),
            'lucky_time' => ['nullable', 'string', 'max:30'],
            ...TranslatedRules::for('lucky_direction', required: false, max: 30),
        ];
    }

    public function messages(): array
    {
        return ['rashi.unique' => 'This rashi already has a rashifal for that date. Edit it instead.'];
    }

    /** Empty star fields are dropped so the app only gets the ones the admin set. */
    public function payload(): array
    {
        $data = $this->validated();
        $data['stars'] = array_map('intval', array_filter($data['stars'] ?? [], fn ($v) => $v !== null && $v !== ''));

        return $data;
    }
}
