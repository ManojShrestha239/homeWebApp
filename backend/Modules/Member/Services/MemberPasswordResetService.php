<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Member\Models\Member;
use Shared\Exceptions\ApiException;
use Shared\Utils\HttpStatus;
use Throwable;

final class MemberPasswordResetService
{
    public function __construct(
        private readonly MemberPasswordResetMailer $mailer,
    ) {}

    /**
     * Step 1: create a reset token and email it.
     * Returns nothing on purpose. The caller always sends the same reply,
     * so attackers can't tell whether the email is registered.
     */
    public function requestReset(string $email): void
    {
        $member = Member::where('email', strtolower(trim($email)))->first();

        // Unknown email: do nothing, and the caller still replies the same way
        if ($member === null) {
            return;
        }

        $plainToken = Str::random(64);

        $member->forceFill([
            'password_reset_token' => hash('sha256', $plainToken),
            'password_reset_expires_at' => now()->addMinutes(30),
        ])->save();

        // Email failures are logged, not shown to the user. The reply stays generic.
        try {
            $this->mailer->send($member, $plainToken);
        } catch (Throwable $e) {
            Log::warning('Password reset email failed to send', [
                'member_id' => $member->id,
                'exception' => $e,
            ]);
        }
    }

    /**
     * Step 2: check the token, set the new password, and make the token unusable.
     */
    public function resetPassword(string $plainToken, string $newPassword): void
    {
        $hash = hash('sha256', $plainToken);

        try {
            DB::transaction(function () use ($hash, $newPassword): void {
                // lockForUpdate stops two requests from using the same token at once
                $member = Member::where('password_reset_token', $hash)
                    ->lockForUpdate()
                    ->first();

                if ($member === null) {
                    throw new ApiException(
                        HttpStatus::BAD_REQUEST,
                        'INVALID_RESET_TOKEN',
                        'This password reset link is not valid.',
                        'Request a new link from the forgot password page.',
                    );
                }

                $isExpired = $member->password_reset_expires_at?->isPast() ?? true;
                if ($isExpired) {
                    throw new ApiException(
                        HttpStatus::BAD_REQUEST,
                        'RESET_TOKEN_EXPIRED',
                        'This password reset link has expired.',
                        'Request a new link from the forgot password page.',
                    );
                }

                // The password cast hashes this automatically
                // Clear the token so the link works only once
                // Opening the email link proves the address is ours, so mark it verified too
                $member->forceFill([
                    'password' => $newPassword,
                    'password_reset_token' => null,
                    'password_reset_expires_at' => null,
                    'email_verified_at' => $member->email_verified_at ?? now(),
                ])->save();
            });
        } catch (ApiException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Password reset failed', ['exception' => $e]);

            throw new ApiException(
                HttpStatus::INTERNAL_SERVER_ERROR,
                'PASSWORD_RESET_FAILED',
                'We could not reset your password. Please try again.',
                null,
                $e,
            );
        }
    }
}
