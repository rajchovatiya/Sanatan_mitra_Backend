<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['date', 'text', 'author', 'is_active'])]
class Suvichar extends Model
{
    use HasTranslations;

    protected $attributes = ['is_active' => true];

    protected function casts(): array
    {
        return ['date' => 'date:Y-m-d', 'text' => 'array', 'author' => 'array', 'is_active' => 'boolean'];
    }
}
