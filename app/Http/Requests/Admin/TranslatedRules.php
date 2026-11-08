<?php

namespace App\Http\Requests\Admin;

/** Rules for a {"en","hi","gu"} text field: English required (or optional), others optional. */
final class TranslatedRules
{
    public static function for(string $field, bool $required = true, int $max = 255): array
    {
        $text = $max > 0 ? ['string', "max:{$max}"] : ['string'];

        return [
            $field => [$required ? 'required' : 'nullable', 'array:'.implode(',', config('sanatan.languages'))],
            "{$field}.en" => [$required ? 'required' : 'nullable', ...$text],
            "{$field}.hi" => ['nullable', ...$text],
            "{$field}.gu" => ['nullable', ...$text],
        ];
    }
}
