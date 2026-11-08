<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

#[Fillable(['type', 'name', 'description', 'icon_path', 'sort_order', 'is_active'])]
class Category extends Model
{
    use HasTranslations;

    /** Module => label. Add a type here to give a new module categories. */
    public const TYPES = [
        'poster' => 'Posters', 'mantra' => 'Mantras', 'muhurat' => 'Muhurat types',
        'wallpaper' => 'Wallpapers', 'video' => 'Video templates', 'course' => 'Courses',
    ];

    /** Relations that hold a category's items; a category with any of them cannot be deleted. */
    public const ITEM_RELATIONS = ['posters', 'mantras', 'muhuratDates', 'studioMedia', 'learningVideos'];

    protected $attributes = ['sort_order' => 0, 'is_active' => true];

    protected function casts(): array
    {
        return ['name' => 'array', 'description' => 'array', 'is_active' => 'boolean'];
    }

    protected static function booted(): void
    {
        $forget = function (Category $c) {
            foreach (config('sanatan.languages') as $lang) {
                Cache::forget(self::cacheKey($c->type, $lang));
            }
        };
        static::saved($forget);
        static::deleted($forget);
    }

    public static function cacheKey(string $type, string $lang): string
    {
        return "categories:{$type}:{$lang}";
    }

    public function scopeOfType(Builder $q, string $type): void
    {
        $q->where('type', $type);
    }

    public function scopeVisible(Builder $q): void
    {
        $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function posters(): HasMany
    {
        return $this->hasMany(Poster::class);
    }

    public function mantras(): HasMany
    {
        return $this->hasMany(Mantra::class);
    }

    public function muhuratDates(): HasMany
    {
        return $this->hasMany(MuhuratDate::class);
    }

    public function studioMedia(): HasMany
    {
        return $this->hasMany(StudioMedia::class);
    }

    public function learningVideos(): HasMany
    {
        return $this->hasMany(LearningVideo::class);
    }

    public function hasItems(): bool
    {
        foreach (self::ITEM_RELATIONS as $relation) {
            if ($this->{$relation}()->exists()) {
                return true;
            }
        }

        return false;
    }
}
