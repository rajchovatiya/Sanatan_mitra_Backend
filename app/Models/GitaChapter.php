<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

#[Fillable(['name', 'summary'])]
class GitaChapter extends Model
{
    use HasTranslations;

    protected $primaryKey = 'number';

    public $incrementing = false;

    protected function casts(): array
    {
        return ['name' => 'array', 'summary' => 'array'];
    }

    protected static function booted(): void
    {
        static::saved(function () {
            foreach (config('sanatan.languages') as $lang) {
                Cache::forget(self::cacheKey($lang));
            }
        });
    }

    public static function cacheKey(string $lang): string
    {
        return "gita:chapters:{$lang}";
    }

    public function verses(): HasMany
    {
        return $this->hasMany(GitaVerse::class, 'chapter', 'number');
    }
}
