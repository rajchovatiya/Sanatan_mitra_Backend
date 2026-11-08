<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

/** Shared parts of the admin content forms. Access is checked by the route's can:manage-content. */
abstract class ContentRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // An unticked checkbox is not sent at all.
        $this->merge(['is_active' => $this->boolean('is_active'), 'sort_order' => $this->input('sort_order', 0) ?? 0]);
    }

    protected function isCreate(): bool
    {
        return $this->isMethod('post');
    }

    protected function common(): array
    {
        return [
            'sort_order' => ['integer', 'between:0,65000'],
            'is_active' => ['boolean'],
        ];
    }

    protected function imageRules(bool $required): array
    {
        return [$required ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:3072'];
    }
}
