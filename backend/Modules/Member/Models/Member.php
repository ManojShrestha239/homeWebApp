<?php

declare(strict_types=1);

namespace Modules\Member\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;   // NEW: the package's "this can hold a wristband" contract

final class Member extends Authenticatable implements JWTSubject   // NEW: add implements
{
    use Notifiable;

    protected $table = 'members';

    protected $fillable = [
        'full_name',
        'email',
        'password',
        'email_verification_token',
        'email_verification_expires_at',
    ];

    protected $hidden = [
        'password',
        'email_verification_token',
        'remember_token',
    ];

    /**
     * NEW: the value stored inside the JWT as "sub" (subject).
     * The member's primary key is a safe, unique choice.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * NEW: extra data to put inside the token. We keep it empty so tokens stay small
     * and never carry private data like email.
     *
     * @return array<string, mixed>
     */
    public function getJWTCustomClaims(): array
    {
        return [];
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'email_verification_expires_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
