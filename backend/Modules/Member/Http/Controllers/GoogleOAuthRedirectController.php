<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
    public function __invoke(Request $request): JsonResponse
    {
        $state = Str::random(40);

        // Store the remember choice with the state, so the callback can use it.
        // The state is one-time, so this value is also one-time.
        Cache::put(
            MemberOAuthService::stateKey($state),
            ['remember' => $request->boolean('remember')],
            now()->addMinutes(10),
        );

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
