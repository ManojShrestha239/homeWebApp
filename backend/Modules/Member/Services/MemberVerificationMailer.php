<?php

declare(strict_types=1);

namespace Modules\Member\Services;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;
use Modules\Member\Models\Member;

/**
 * Knows how to send the "verify your email" message. Plain text on purpose: the backend never renders HTML.
 */
final class MemberVerificationMailer
{
    public function send(Member $member, string $plainToken): void
    {
        // The link opens the FRONTEND page, which then calls the verify API (built in a later step)
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $link = $frontendUrl . '/verify-email?token=' . $plainToken;

        $body = "Hi {$member->full_name},\n\n"
            . "Please verify your email address by opening this link:\n{$link}\n\n"
            . "This link expires in 60 minutes.";

        Mail::raw($body, static function (Message $message) use ($member): void {
            $message->to($member->email)->subject('Verify your email address');
        });
    }
}
