<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A wallpaper (image) or a video template (thumbnail + video file). */
#[Fillable(['kind', 'category_id', 'title', 'image_path', 'video_path', 'sort_order', 'is_active'])]
class StudioMedia extends Model
{
    use HasTranslations;

    public const KINDS = ['wallpaper' => 'Wallpapers', 'video' => 'Video templates'];

    protected $attributes = ['sort_order' => 0, 'is_active' => true];

    protected function casts(): array
    {
        return ['title' => 'array', 'is_active' => 'boolean'];
    }

    public function scopeVisible(Builder $q): void
    {
        $q->where('is_active', true)->orderBy('sort_order')->orderByDesc('id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function toApi(): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'title' => $this->tr('title'),
            'image_url' => Media::url($this->image_path),
            'video_url' => Media::url($this->video_path),
        ];
    }
}
