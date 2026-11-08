<?php

namespace App\Http\Requests\Admin;

use App\Models\GitaChapter;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Gita chapter (name/summary) or verse, depending on the route. */
class GitaRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->routeIs('admin.gita-verses.*')) {
            $this->merge(['is_featured' => $this->boolean('is_featured')]);
        }
    }

    public function rules(): array
    {
        if (! $this->routeIs('admin.gita-verses.*')) {
            return [
                ...TranslatedRules::for('name', max: 100),
                ...TranslatedRules::for('summary', required: false, max: 0),
            ];
        }

        $max = GitaChapter::find($this->integer('chapter'))?->verse_count ?? 1;

        return [
            'chapter' => ['required', 'integer', 'exists:gita_chapters,number'],
            'verse' => [
                'required', 'integer', 'between:1,'.$max,
                Rule::unique('gita_verses')->where('chapter', $this->integer('chapter'))->ignore($this->route('gita_verse')),
            ],
            'sanskrit' => ['required', 'string', 'max:2000'],
            'transliteration' => ['nullable', 'string', 'max:2000'],
            ...TranslatedRules::for('meaning', max: 0),
            'is_featured' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return ['verse.unique' => 'This verse is already added. Edit it instead.'];
    }
}
