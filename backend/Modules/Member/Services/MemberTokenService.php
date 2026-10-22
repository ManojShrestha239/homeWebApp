<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Modules\Member\Models\Member;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

/**
 * The one place that creates JWTs for members.
 * Password login and Google login both use it, so the rules match.
 */
final class MemberTokenService
{
    /**
     * @return array{access_token: string, expires_in: int}
     */
    public function issue(Member $member, bool $remember): array
    {
        $ttlMinutes = $remember
            ? (int) config('member_auth.remember_ttl_minutes')
            : (int) config('member_auth.ttl_minutes');

        // Remember the package's default TTL so we can put it back afterwards
        $defaultTtl = (int) JWTAuth::factory()->getTTL();

        try {
            // Set the TTL for this one token only
            JWTAuth::factory()->setTTL($ttlMinutes);
            $token = JWTAuth::fromUser($member);
        } finally {
            // Always restore the default, even if token creation fails
            JWTAuth::factory()->setTTL($defaultTtl);
        }

        return [
            'access_token' => $token,
            'expires_in' => $ttlMinutes * 60,   // the API reports seconds
        ];
    }
}
