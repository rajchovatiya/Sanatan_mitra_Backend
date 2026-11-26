<?php

namespace App\Models;

use App\Models\Concerns\ClearsHomeCache;
use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['is_vrat', 'name', 'tithi', 'about', 'rituals', 'date', 'image_path', 'sort_order', 'is_active'])]
class Festival extends Model
{
    use ClearsHomeCache, HasTranslations;

    protected $attributes = ['is_vrat' => false, 'sort_order' => 0, 'is_active' => true];

    protected function casts(): array
    {
        return [
            'is_vrat' => 'boolean', 'name' => 'array', 'tithi' => 'array', 'about' => 'array',
            'rituals' => 'array', 'date' => 'date:Y-m-d', 'is_active' => 'boolean',
        ];
    }

    public function scopeVisible(Builder $q): void
    {
        $q->where('is_active', true)->orderBy('date')->orderBy('sort_order');
    }

    /** Ritual steps in the current language, one per line in the admin form. */
    public function ritualSteps(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $this->tr('rituals') ?? ''))));
    }
}
