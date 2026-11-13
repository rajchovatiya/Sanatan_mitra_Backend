<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $guarded = [];

    protected $casts = ['lat' => 'float', 'lng' => 'float', 'population' => 'integer'];

    public static function searchKey(string $text): string
    {
        $ascii = function_exists('transliterator_transliterate')
            ? (transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text) ?: $text)
            : strtolower($text);

        return trim(preg_replace('/[^a-z0-9 ]+/', '', strtolower($ascii)) ?? '');
    }

    /** The shape the app reads. */
    public function toApi(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'district' => $this->district,
            'state' => $this->state,
            'country_code' => $this->country_code,
            'lat' => $this->lat,
            'lng' => $this->lng,
            'timezone' => $this->timezone,
        ];
    }
}
