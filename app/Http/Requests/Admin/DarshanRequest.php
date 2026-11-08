<?php

namespace App\Http\Requests\Admin;

class DarshanRequest extends ContentRequest
{
    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'temple_id' => ['nullable', 'exists:temples,id'],
            ...TranslatedRules::for('title', max: 150),
            'image' => $this->imageRules($this->isCreate()),
            // Only https links, e.g. a YouTube live stream.
            'video_url' => ['nullable', 'url:https', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
