<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** Admin panel user. super_admin manages everything; editor manages content only. */
#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class Admin extends Authenticatable
{
    public const ROLE_SUPER = 'super_admin';

    public const ROLE_EDITOR = 'editor';

    public const ROLES = [self::ROLE_SUPER, self::ROLE_EDITOR];

    /** Same as the column defaults, so a just-created admin is complete without a reload. */
    protected $attributes = ['role' => self::ROLE_EDITOR, 'is_active' => true];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function isSuper(): bool
    {
        return $this->role === self::ROLE_SUPER;
    }
}
