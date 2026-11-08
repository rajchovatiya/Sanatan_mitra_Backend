<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['chapter', 'verse', 'sanskrit', 'transliteration', 'meaning', 'is_featured'])]
class GitaVerse extends Model
{
    use HasTranslations;

    protected $attributes = ['is_featured' => false];

    protected function casts(): array
    {
        return ['chapter' => 'integer', 'verse' => 'integer', 'meaning' => 'array', 'is_featured' => 'boolean'];
    }

    public function toApi(): array
    {
        return [
            'chapter' => $this->chapter,
            'verse' => $this->verse,
            'sanskrit' => $this->sanskrit,
            'transliteration' => $this->transliteration,
            'meaning' => $this->tr('meaning'),
        ];
    }
}
