<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Modules\Member\Services\MemberOAuthService;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

/**
 * GET /api/v1/members/oauth/google/redirect
 * Returns the Google sign-in URL as JSON. The frontend sends the browser there.
 */
final class GoogleOAuthRedirectController
{
    public function __invoke(): JsonResponse
    {
        // A random "state" value proves that the callback belongs to THIS sign-in attempt
        $state = Str::random(40);

        // Remember it for 10 minutes. The callback must find it, and it can be used only once
        Cache::put(MemberOAuthService::stateKey($state), true, now()->addMinutes(10));

        // stateless() stops Socialite from using the session (we are an API, with no session).
        // with(['state' => ...]) sends our own state value to Google.
        $url = Socialite::driver('google')
            ->stateless()
            ->with(['state' => $state])
            ->redirect()
            ->getTargetUrl();

        return ApiResponse::make(
            status: HttpStatus::OK,
            type: 'GOOGLE_AUTH_URL',
            message: 'Continue to Google to sign in.',
            data: ['url' => $url],
        );
    }
}
