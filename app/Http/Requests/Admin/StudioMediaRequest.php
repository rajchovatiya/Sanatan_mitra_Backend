<?php

namespace App\Http\Requests\Admin;

use App\Models\StudioMedia;
use Illuminate\Validation\Rule;

class StudioMediaRequest extends ContentRequest
{
    public function rules(): array
    {
        $kind = $this->input('kind');

        return [
            'kind' => ['required', Rule::in(array_keys(StudioMedia::KINDS))],
            // The category must belong to the same module (wallpaper or video).
            'category_id' => ['required', Rule::exists('categories', 'id')->where('type', is_string($kind) ? $kind : '')],
            ...TranslatedRules::for('title', max: 120),
            'image' => $this->imageRules($this->isCreate()),
            // Video templates: mp4 up to 30 MB.
            'video' => [$this->isCreate() && $kind === 'video' ? 'required' : 'nullable', 'file', 'mimes:mp4', 'max:30720'],
            ...$this->common(),
        ];
    }
}
