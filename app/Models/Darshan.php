<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['date', 'temple_id', 'title', 'image_path', 'video_url', 'is_active'])]
class Darshan extends Model
{
    use HasTranslations;

    protected $attributes = ['is_active' => true];

    protected function casts(): array
    {
        return ['date' => 'date:Y-m-d', 'title' => 'array', 'is_active' => 'boolean'];
    }

    public function temple(): BelongsTo
    {
        return $this->belongsTo(Temple::class);
    }
}
