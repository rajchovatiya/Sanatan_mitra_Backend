<?php

namespace App\Models;

use App\Models\Concerns\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** Fixed pages (privacy, terms, about); created by the migration, edited by the admin. */
#[Fillable(['title', 'body'])]
class Page extends Model
{
    use HasTranslations;

    protected $primaryKey = 'slug';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return ['title' => 'array', 'body' => 'array'];
    }
}
