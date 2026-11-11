<?php

namespace App\Services\Translation;

/**
 * Machine translation for admin content (English to Hindi / Gujarati).
 * The admin always reviews and can edit the result before saving.
 *
 * @throws TranslationUnavailable when no service is set up or it fails.
 */
interface Translator
{
    /**
     * @param  list<string>  $texts  English texts
     * @return list<string> translations in the same order
     */
    public function translate(array $texts, string $target): array;
}
