<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Modules\Member\Models\Member;

/**
 * Sends the "reset your password" email. Plain text, because the backend never renders HTML.
 */
final class MemberPasswordResetMailer
{
    public function send(Member $member, string $plainToken): void
    {
        // The link opens the React page, which then posts the token and the new password
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $link = $frontendUrl . '/reset-password?token=' . $plainToken;

        $body = "Hi {$member->full_name},\n\n"
            . "We received a request to reset your password. Open this link to choose a new one:\n{$link}\n\n"
            . "This link expires in 30 minutes. If you did not ask for this, you can ignore this email.";

        Mail::raw($body, static function (Message $message) use ($member): void {
            $message->to($member->email)->subject('Reset your password');
        });
    }
}
