<?php

namespace App\Models\Concerns;

/** For json text columns holding {"en": ..., "hi": ..., "gu": ...}. */
trait HasTranslations
{
    /** Text in the current language, falling back to English. */
    public function tr(string $attribute): ?string
    {
        $values = $this->{$attribute} ?? [];

        return ($values[app()->getLocale()] ?? null) ?: ($values['en'] ?? null);
    }
}
