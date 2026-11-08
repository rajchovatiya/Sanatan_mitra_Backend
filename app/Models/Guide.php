<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use App\Support\Media;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** One flexible format for the reference modules, so they share one admin screen and one API. */
#[Fillable(['module', 'title', 'subtitle', 'body', 'facts', 'points', 'image_path', 'sort_order', 'is_active'])]
class Guide extends Model
{
    use HasTranslations;

    public const MODULES = [
        'gems' => 'Gemstones', 'rudraksha' => 'Rudraksha', 'tarot' => 'Tarot cards',
        'numerology' => 'Numerology', 'palmistry' => 'Palmistry', 'graphology' => 'Graphology',
    ];

    protected $attributes = ['sort_order' => 0, 'is_active' => true];

    protected function casts(): array
    {
        return [
            'title' => 'array', 'subtitle' => 'array', 'body' => 'array', 'facts' => 'array', 'points' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeVisible(Builder $q): void
    {
        $q->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    public function toApi(bool $detail): array
    {
        $base = [
            'id' => $this->id,
            'module' => $this->module,
            'title' => $this->tr('title'),
            'subtitle' => $this->tr('subtitle'),
            'image_url' => Media::url($this->image_path),
        ];

        if (! $detail) {
            return $base;
        }

        $facts = [];
        foreach ($this->lines('facts') as $line) {
            [$label, $value] = array_pad(array_map('trim', explode(':', $line, 2)), 2, '');
            $facts[] = ['label' => $label, 'value' => $value];
        }

        return $base + ['body' => $this->tr('body'), 'facts' => $facts, 'points' => $this->lines('points')];
    }

    private function lines(string $attribute): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', $this->tr($attribute) ?? ''))));
    }
}
