<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Member\DTOs\LoginMemberDTO;
use Modules\Member\Models\Member;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Shared\Exceptions\ApiException;
use Shared\Utils\HttpStatus;
use Throwable;

/**
 * Checks the password, checks the email is verified, then issues a JWT.
 */
final class MemberLoginService
{
    /**
     * @return array{access_token: string, token_type: string, expires_in: int, member: Member}
     */
    public function login(LoginMemberDTO $dto): array
    {
        $member = Member::where('email', $dto->email)->first();

        // Same message for "no such email" and "wrong password".
        // This stops attackers from discovering which emails are registered.
        if ($member === null || ! Hash::check($dto->password, $member->password)) {
            throw new ApiException(
                HttpStatus::UNAUTHORIZED,
                'INVALID_CREDENTIALS',
                'The email or password is incorrect.',
            );
        }

        // The password was right, so it's safe to explain the next step
        if ($member->email_verified_at === null) {
            throw new ApiException(
                HttpStatus::FORBIDDEN,
                'EMAIL_NOT_VERIFIED',
                'Please verify your email before logging in.',
                'Open the verification link we sent to your inbox.',
            );
        }

        try {
            $token = JWTAuth::fromUser($member);   // create the signed wristband

            return [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => (int) JWTAuth::factory()->getTTL() * 60,   // TTL is in minutes; we return seconds
                'member' => $member,
            ];
        } catch (Throwable $e) {
            Log::error('Token creation failed', ['exception' => $e]);

            throw new ApiException(
                HttpStatus::INTERNAL_SERVER_ERROR,
                'LOGIN_FAILED',
                'We could not log you in. Please try again.',
                null,
                $e,
            );
        }
    }
}
