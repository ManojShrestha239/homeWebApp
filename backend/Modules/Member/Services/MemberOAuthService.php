<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Modules\Member\Models\Member;
use Shared\Exceptions\ApiException;
use Shared\Utils\HttpStatus;

/**
 * Turns a verified Google profile into a Member, then issues our JWT.
 */
final class MemberOAuthService
{
    /** Cache key for a one-time "state" value. Used to block forged callbacks (CSRF). */
    public static function stateKey(string $state): string
    {
        return 'oauth:google:state:' . $state;
    }

    public function resolveGoogleMember(SocialiteUser $googleUser): Member
    {
        $googleId = (string) $googleUser->getId();
        $email = strtolower(trim((string) $googleUser->getEmail()));
        $avatar = $googleUser->getAvatar();

        // Google's profile says whether it has confirmed this email address
        $googleVerified = (bool) ($googleUser->getRaw()['verified_email'] ?? false);

        // A transaction plus lockForUpdate() stops two simultaneous sign-ins from creating duplicates
        return DB::transaction(function () use ($googleId, $email, $avatar, $googleVerified, $googleUser): Member {
            // RULE 1: We already know this Google account
            $member = Member::where('google_id', $googleId)->lockForUpdate()->first();
            if ($member !== null) {
                return $member;
            }

            // Every rule below needs Google to have confirmed the email
            if (! $googleVerified) {
                throw new ApiException(
                    HttpStatus::FORBIDDEN,
                    'GOOGLE_EMAIL_NOT_VERIFIED',
                    'Google has not confirmed this email address.',
                    'Verify the email with Google, then try again.',
                );
            }

            // RULE 2: An account with this email already exists (registered with a password)
            $member = Member::where('email', $email)->lockForUpdate()->first();
            if ($member !== null) {
                // Link the Google account. Google has proven ownership, so mark the email verified too
                $member->forceFill([
                    'google_id' => $googleId,
                    'avatar_url' => $avatar,
                    'email_verified_at' => $member->email_verified_at ?? now(),
                ])->save();

                return $member;
            }

            // RULE 3: A brand-new person. Google verified the email, so no email step is needed
            $member = Member::create([
                'full_name' => $googleUser->getName() ?: Str::before($email, '@'),
                'email' => $email,
                'google_id' => $googleId,
                'avatar_url' => $avatar,
            ]);

            // email_verified_at is not fillable (on purpose), so we set it directly
            $member->forceFill(['email_verified_at' => now()])->save();

            return $member;
        });
    }

    /** @return array{access_token: string, expires_in: int} */
    public function issueToken(Member $member, bool $remember): array
    {
        return app(MemberTokenService::class)->issue($member, $remember);
    }
}
