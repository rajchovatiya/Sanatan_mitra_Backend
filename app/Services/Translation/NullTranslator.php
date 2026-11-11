<?php

namespace App\Services\Translation;

/** Used until a translation service key is added. */
class NullTranslator implements Translator
{
    public function translate(array $texts, string $target): array
    {
        throw new TranslationUnavailable('Translation is not set up yet. Add a translation service key in .env.');
    }
}
