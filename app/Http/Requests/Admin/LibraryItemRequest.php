<?php

namespace App\Http\Requests\Admin;

use App\Models\LibraryItem;
use Illuminate\Validation\Rule;

class LibraryItemRequest extends ContentRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(array_keys(LibraryItem::TYPES))],
            ...TranslatedRules::for('title', max: 150),
            ...TranslatedRules::for('subtitle', required: false, max: 150),
            ...TranslatedRules::for('body', max: 0),
            'image' => $this->imageRules(false),
            'audio' => ['nullable', 'file', 'mimes:mp3,m4a,aac', 'max:20480'],
            ...$this->common(),
        ];
    }
}
