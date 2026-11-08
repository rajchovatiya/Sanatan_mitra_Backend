<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Saved birth details (self or family) used for Kundli and Matching. */
#[Fillable(['name', 'relation', 'gender', 'birth_date', 'birth_time', 'birth_place', 'lat', 'lng', 'tz_offset'])]
class Kundli extends Model
{
    /** India (IST) unless the app sends another offset. */
    protected $attributes = ['tz_offset' => 5.5];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date:Y-m-d',
            'lat' => 'float',
            'lng' => 'float',
            'tz_offset' => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
