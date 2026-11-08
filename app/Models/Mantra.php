<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'category_id', 'title', 'deity', 'lyrics', 'meaning', 'image_path', 'audio_path',
    'duration_seconds', 'sort_order', 'is_active',
])]
class Mantra extends Model
{
    use HasTranslations;

    protected $attributes = ['sort_order' => 0, 'is_active' => true];

    protected function casts(): array
    {
        return [
            'title' => 'array', 'deity' => 'array', 'lyrics' => 'array', 'meaning' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeVisible(Builder $q): void
    {
        $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
