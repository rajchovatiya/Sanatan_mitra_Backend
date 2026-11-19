<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['category_id', 'title', 'image_path', 'frame', 'footer_below', 'show_date', 'date_position', 'date_on', 'weekdays', 'today_on', 'show_from', 'sort_order', 'is_active'])]
class Poster extends Model
{
    use HasTranslations;

    public const FRAMES = ['classic', 'bottom_band', 'side_photo', 'minimal'];

    protected $attributes = ['frame' => 'classic', 'footer_below' => false, 'show_date' => false, 'date_position' => 'top_right', 'sort_order' => 0, 'is_active' => true];

    /** Corners where the date badge can sit. */
    /** Days of the week (ISO: 1 = Monday … 7 = Sunday). */
    public const WEEKDAYS = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];

    public const DATE_POSITIONS = ['top_left' => 'Top left', 'top_right' => 'Top right', 'bottom_left' => 'Bottom left', 'bottom_right' => 'Bottom right'];

    protected function casts(): array
    {
        return ['title' => 'array', 'show_from' => 'date:Y-m-d', 'is_active' => 'boolean', 'footer_below' => 'boolean', 'show_date' => 'boolean', 'date_on' => 'date:Y-m-d', 'weekdays' => 'array', 'today_on' => 'date:Y-m-d'];
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
