<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Modules\Member\DTOs\LoginMemberDTO;
use Modules\Member\Models\Member;
use Shared\Exceptions\ApiException;
use Shared\Utils\HttpStatus;
use Throwable;

final class MemberLoginService
{
    public function __construct(
        private readonly MemberTokenService $tokens,
    ) {}

    /**
     * @return array{access_token: string, token_type: string, expires_in: int, member: Member}
     */
    public function login(LoginMemberDTO $dto): array
    {
        $member = Member::where('email', $dto->email)->first();

        if ($member === null || $member->password === null || ! Hash::check($dto->password, $member->password)) {
            throw new ApiException(
                HttpStatus::UNAUTHORIZED,
                'INVALID_CREDENTIALS',
                'The email or password is incorrect.',
            );
        }

        if ($member->email_verified_at === null) {
            throw new ApiException(
                HttpStatus::FORBIDDEN,
                'EMAIL_NOT_VERIFIED',
                'Please verify your email before logging in.',
                'Open the verification link we sent to your inbox.',
            );
        }

        try {
            $issued = $this->tokens->issue($member, $dto->remember);
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

        return [
            'access_token' => $issued['access_token'],
            'token_type' => 'bearer',
            'expires_in' => $issued['expires_in'],
            'member' => $member,
        ];
    }
}
