<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['type', 'title', 'subtitle', 'body', 'image_path', 'audio_path', 'sort_order', 'is_active'])]
class LibraryItem extends Model
{
    use HasTranslations;

    /** Same groups as the app (the Gita has its own tables). */
    public const TYPES = ['katha' => 'Vrat Katha', 'aarti' => 'Aarti & Prayer', 'beej' => 'Beej Mantra', 'chalisa' => 'Chalisa & Stotra'];

    protected $attributes = ['sort_order' => 0, 'is_active' => true];

    protected function casts(): array
    {
        return ['title' => 'array', 'subtitle' => 'array', 'body' => 'array', 'is_active' => 'boolean'];
    }

    public function scopeVisible(Builder $q): void
    {
        $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }
}
