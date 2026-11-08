<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

#[Fillable([
    'date', 'rashi', 'overall', 'love', 'career', 'health', 'money', 'stars',
    'lucky_number', 'lucky_colour', 'lucky_time', 'lucky_direction',
])]
class Rashifal extends Model
{
    use HasTranslations;

    /** Index 0..11, same order as the app. */
    public const RASHIS = [
        'Mesha', 'Vrishabha', 'Mithuna', 'Karka', 'Simha', 'Kanya',
        'Tula', 'Vrishchika', 'Dhanu', 'Makara', 'Kumbha', 'Meena',
    ];

    public const AREAS = ['overall', 'love', 'career', 'health', 'money'];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d', 'rashi' => 'integer', 'stars' => 'array',
            'overall' => 'array', 'love' => 'array', 'career' => 'array', 'health' => 'array', 'money' => 'array',
            'lucky_colour' => 'array', 'lucky_direction' => 'array',
        ];
    }

    protected static function booted(): void
    {
        $forget = function (Rashifal $r) {
            foreach (array_unique([$r->date->toDateString(), $r->getOriginal('date')?->toDateString()]) as $date) {
                foreach (config('sanatan.languages') as $lang) {
                    Cache::forget(self::cacheKey((string) $date, $lang));
                }
            }
        };
        static::saved($forget);
        static::deleted($forget);
    }

    public static function cacheKey(string $date, string $lang): string
    {
        return "rashifal:{$date}:{$lang}";
    }

    public function rashiName(): string
    {
        return self::RASHIS[$this->rashi] ?? '?';
    }
}
