<?php

namespace App\Services\Translation;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

/** Google Cloud Translation (v2, API key). */
class GoogleTranslator implements Translator
{
    public function __construct(private string $key) {}

    public function translate(array $texts, string $target): array
    {
        try {
            $rows = Http::timeout(20)
                ->post('https://translation.googleapis.com/language/translate/v2?key='.urlencode($this->key), [
                    'q' => array_values($texts),
                    'source' => 'en',
                    'target' => $target,
                    'format' => 'text',
                ])
                ->throw()
                ->json('data.translations');
        } catch (RequestException|ConnectionException) {
            throw new TranslationUnavailable('The translation service did not answer. Please try again.');
        }

        return array_map(fn (array $row) => html_entity_decode($row['translatedText'], ENT_QUOTES), $rows ?? []);
    }
}
