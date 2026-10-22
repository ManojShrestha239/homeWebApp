<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Member\Models\Member;
use Shared\Exceptions\ApiException;
use Shared\Utils\HttpStatus;
use Throwable;

/**
 * Handles the "click the link" step: checks the token and marks the email as verified.
 */
final class MemberEmailVerificationService
{
    public function verify(string $plainToken): Member
    {
        // The email contains the plain token. The database only stores its SHA-256 hash.
        $hash = hash('sha256', $plainToken);

        try {
            // A transaction plus lockForUpdate() stops two clicks at the same moment
            // from both trying to verify the same member.
            return DB::transaction(function () use ($hash): Member {
                $member = Member::where('email_verification_token', $hash)
                    ->lockForUpdate()
                    ->first();

                // No member has this token: the link is fake, copied wrong, or already replaced
                if ($member === null) {
                    throw new ApiException(
                        HttpStatus::BAD_REQUEST,
                        'INVALID_VERIFICATION_TOKEN',
                        'This verification link is not valid.',
                    );
                }

                // Already verified: clicking the link twice is harmless, so treat it as success
                if ($member->email_verified_at !== null) {
                    return $member;
                }

                // Expired (or no expiry date at all): ask for a new link instead
                $isExpired = $member->email_verification_expires_at?->isPast() ?? true;
                if ($isExpired) {
                    throw new ApiException(
                        HttpStatus::BAD_REQUEST,
                        'VERIFICATION_TOKEN_EXPIRED',
                        'This verification link has expired.',
                        'Please request a new verification email.',
                    );
                }

                // Everything checks out: record the time the email was verified
                $member->forceFill(['email_verified_at' => now()])->save();

                return $member;
            });
        } catch (ApiException $e) {
            throw $e;   // our own known errors pass straight through to the controller
        } catch (Throwable $e) {
            Log::error('Email verification failed', ['exception' => $e]);

            throw new ApiException(
                HttpStatus::INTERNAL_SERVER_ERROR,
                'VERIFICATION_FAILED',
                'We could not verify your email. Please try again.',
                null,
                $e,
            );
        }
    }
}
