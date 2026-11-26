<?php

namespace App\Models;

use App\Models\Concerns\ClearsHomeCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'type', 'name', 'location', 'state', 'deity', 'about', 'timings', 'best_time',
    'lat', 'lng', 'image_path', 'sort_order', 'is_active',
])]
class Temple extends Model
{
    use ClearsHomeCache, HasTranslations;

    public const TYPES = ['jyotirlinga' => 'Jyotirlinga', 'shakti_peeth' => 'Shakti Peeth', 'char_dham' => 'Char Dham', 'other' => 'Other'];

    protected $attributes = ['type' => 'other', 'sort_order' => 0, 'is_active' => true];

    protected function casts(): array
    {
        return [
            'name' => 'array', 'location' => 'array', 'deity' => 'array', 'about' => 'array',
            'timings' => 'array', 'best_time' => 'array', 'lat' => 'float', 'lng' => 'float', 'is_active' => 'boolean',
        ];
    }

    public function scopeVisible(Builder $q): void
    {
        $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
