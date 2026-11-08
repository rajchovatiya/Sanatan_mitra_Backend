<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A live stream (kind "live") or a course lesson (kind "lesson", belongs to a course category). */
#[Fillable(['kind', 'category_id', 'title', 'description', 'video_url', 'thumbnail_path', 'sort_order', 'is_active'])]
class LearningVideo extends Model
{
    use HasTranslations;

    public const KINDS = ['live' => 'Live', 'lesson' => 'Course lessons'];

    protected $attributes = ['sort_order' => 0, 'is_active' => true];

    protected function casts(): array
    {
        return ['title' => 'array', 'description' => 'array', 'is_active' => 'boolean'];
    }

    public function scopeVisible(Builder $q): void
    {
        $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function toApi(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->tr('title'),
            'description' => $this->tr('description'),
            'video_url' => $this->video_url,
            'thumbnail_url' => Media::url($this->thumbnail_path),
        ];
    }
}
