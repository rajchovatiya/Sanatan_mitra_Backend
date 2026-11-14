<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['category_id', 'title', 'image_path', 'frame', 'footer_below', 'show_from', 'sort_order', 'is_active'])]
class Poster extends Model
{
    use HasTranslations;

    public const FRAMES = ['classic', 'bottom_band', 'side_photo', 'minimal'];

    protected $attributes = ['frame' => 'classic', 'footer_below' => false, 'sort_order' => 0, 'is_active' => true];

    protected function casts(): array
    {
        return ['title' => 'array', 'show_from' => 'date:Y-m-d', 'is_active' => 'boolean', 'footer_below' => 'boolean'];
    }

    /** Active, already released, in admin order. */
    public function scopeVisible(Builder $q): void
    {
        $q->where('is_active', true)
            ->where(fn (Builder $w) => $w->whereNull('show_from')->orWhere('show_from', '<=', today()))
            ->orderBy('sort_order')->orderByDesc('id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
