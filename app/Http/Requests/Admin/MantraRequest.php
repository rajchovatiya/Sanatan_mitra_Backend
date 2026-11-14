<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class MantraRequest extends ContentRequest
{
    public function rules(): array
    {
        return [
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', 'mantra')],
            ...TranslatedRules::for('title', max: 120),
            ...TranslatedRules::for('deity', max: 60),
            // Optional: without lyrics the app shows the deity's picture while the audio plays.
            ...TranslatedRules::for('lyrics', required: false, max: 0),
            ...TranslatedRules::for('meaning', required: false, max: 0),
            'image' => $this->imageRules(false),
            // Up to 20 MB; the server's PHP upload limit must allow this.
            'audio' => ['nullable', 'file', 'mimes:mp3,m4a,aac', 'max:20480'],
            'duration_seconds' => ['nullable', 'integer', 'between:1,36000'],
            ...$this->common(),
        ];
    }
}
