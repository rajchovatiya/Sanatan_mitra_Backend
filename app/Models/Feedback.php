<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'message', 'contact', 'platform', 'app_version'])]
class Feedback extends Model
{
    protected $table = 'feedback';

    protected $attributes = ['is_seen' => false];

    protected function casts(): array
    {
        return ['is_seen' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
