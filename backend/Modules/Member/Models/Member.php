<?php

declare(strict_types=1);

namespace Modules\Member\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Authenticatable is Laravel's base "user" class. We use it now so JWT and OAuth login
 * can be added later without changing this model.
 */
final class Member extends Authenticatable
{
    use Notifiable;

    protected $table = 'members';

    /** Columns that mass assignment (Member::create([...])) is allowed to fill */
    protected $fillable = [
        'full_name',
        'email',
        'password',
        'email_verification_token',
        'email_verification_expires_at',
    ];

    /** Columns that must NEVER appear in JSON output */
    protected $hidden = [
        'password',
        'email_verification_token',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'password' => 'hashed',   // automatically hashes the password when it is saved
        ];
    }
}
