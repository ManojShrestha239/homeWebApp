<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Member\DTOs\RegisterMemberDTO;
use Modules\Member\Models\Member;
use Shared\Exceptions\ApiException;
use Shared\Utils\HttpStatus;
use Throwable;

final class MemberRegistrationService
{
    public function __construct(
        private readonly MemberVerificationMailer $mailer,
    ) {}

    public function register(RegisterMemberDTO $dto): Member
    {
        // STEP A: Friendly early check. This runs OUTSIDE the try block on purpose,
        // so our own ApiException is not caught by the generic Throwable handler below.
        if (Member::where('email', $dto->email)->exists()) {
            throw new ApiException(
                HttpStatus::CONFLICT,
                'EMAIL_ALREADY_REGISTERED',
                'This email is already registered.',
            );
        }

        $plainToken = Str::random(64);

        try {
            // STEP B: Save inside a transaction. The unique index on `email` is the final
            // safety net if two requests slip past STEP A at the same moment.
            $member = DB::transaction(function () use ($dto, $plainToken): Member {
                return Member::create([
                    'full_name' => $dto->fullName,
                    'email' => $dto->email,
                    'password' => $dto->password,
                    'email_verification_token' => hash('sha256', $plainToken),
                    'email_verification_expires_at' => now()->addMinutes(60),
                ]);
            });
        } catch (QueryException $e) {
            // UniqueConstraintViolationException extends QueryException, so this catches it too.
            // Only a duplicate on the email column becomes a 409.
            if ($this->isDuplicateEmail($e)) {
                throw new ApiException(
                    HttpStatus::CONFLICT,
                    'EMAIL_ALREADY_REGISTERED',
                    'This email is already registered.',
                    null,
                    $e,
                );
            }

            Log::error('Member registration failed', ['exception' => $e]);
            throw new ApiException(
                HttpStatus::INTERNAL_SERVER_ERROR,
                'REGISTRATION_FAILED',
                'We could not create your account. Please try again.',
                null,
                $e,
            );
        } catch (Throwable $e) {
            Log::error('Member registration failed', ['exception' => $e]);
            throw new ApiException(
                HttpStatus::INTERNAL_SERVER_ERROR,
                'REGISTRATION_FAILED',
                'We could not create your account. Please try again.',
                null,
                $e,
            );
        }

        // STEP C: Send the email only after the save is committed.
        try {
            $this->mailer->send($member, $plainToken);
        } catch (Throwable $e) {
            // The account exists, so registration still succeeds. Log the failure for later.
            Log::warning('Verification email failed to send', [
                'member_id' => $member->id,
                'exception' => $e,
            ]);
        }

        return $member;
    }

    /**
     * Checks the database error message for a duplicate on the members.email column.
     * Each database (SQLite, MySQL, PostgreSQL) words this error differently, so we check all three.
     */
    private function isDuplicateEmail(QueryException $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, 'members.email')
            && (
                str_contains($message, 'UNIQUE constraint failed')   // SQLite
                || str_contains($message, 'Duplicate entry')          // MySQL
                || str_contains($message, 'duplicate key value')      // PostgreSQL
            );
    }
}
