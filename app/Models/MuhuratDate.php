<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['category_id', 'date', 'start_time', 'end_time', 'note', 'is_active'])]
class MuhuratDate extends Model
{
    use HasTranslations;

    protected $attributes = ['is_active' => true];

    protected function casts(): array
    {
        return ['date' => 'date:Y-m-d', 'note' => 'array', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function toApi(): array
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'start_time' => $this->start_time ? substr($this->start_time, 0, 5) : null,
            'end_time' => $this->end_time ? substr($this->end_time, 0, 5) : null,
            'note' => $this->tr('note'),
        ];
    }
}
