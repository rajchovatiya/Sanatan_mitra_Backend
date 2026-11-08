<?php

namespace App\Models;

use App\Services\AppConfig;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected $primaryKey = 'key';

    protected $keyType = 'string';

    public $incrementing = false;

    protected function casts(): array
    {
        return ['value' => 'json'];
    }

    protected static function booted(): void
    {
        // The app config is cached, so drop the cache whenever a setting changes.
        static::saved(fn () => AppConfig::forget());
        static::deleted(fn () => AppConfig::forget());
    }
}
