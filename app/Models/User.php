<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

/** An app user. Signs in with mobile OTP; status and last_login_at are set by the server only. */
#[Fillable([
    'mobile', 'name', 'gender', 'birth_date', 'birth_time', 'birth_place', 'birth_lat', 'birth_lng',
    'city', 'rashi', 'language', 'notifications_enabled',
    'business_type', 'business_name', 'business_address', 'other_details',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_BLOCKED = 'blocked';

    /** Same as the column defaults, so a new user's response is complete without a reload. */
    protected $attributes = [
        'language' => 'en',
        'notifications_enabled' => true,
        'status' => self::STATUS_ACTIVE,
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date:Y-m-d',
            'birth_lat' => 'float',
            'birth_lng' => 'float',
            'rashi' => 'integer',
            'notifications_enabled' => 'boolean',
            'last_login_at' => 'datetime',
            'notifications_read_at' => 'datetime',
        ];
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function kundlis(): HasMany
    {
        return $this->hasMany(Kundli::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function isBlocked(): bool
    {
        return $this->status === self::STATUS_BLOCKED;
    }
}
