<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Modules\Member\Services\MemberOAuthService;
use Shared\Exceptions\ApiException;
use Shared\Utils\HttpStatus;
use Throwable;

/**
 * GET /api/v1/members/oauth/google/callback
 * Google's browser redirect lands here. This is a redirect, not an HTML page.
 * Success and failure both send the browser back to the React app.
 */
final class GoogleOAuthCallbackController
{
    public function __construct(
        private readonly MemberOAuthService $oauth,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $frontendCallback = rtrim((string) config('app.frontend_url'), '/') . '/oauth/callback';

        try {
            // The user clicked "Cancel" on Google's screen
            if ($request->query('error') !== null) {
                throw new ApiException(
                    HttpStatus::BAD_REQUEST,
                    'GOOGLE_SIGNIN_CANCELLED',
                    'Google sign-in was cancelled.',
                );
            }

            // Check the state. Cache::pull reads AND deletes it, so it works only once
            $state = (string) $request->query('state', '');
            if ($state === '' || Cache::pull(MemberOAuthService::stateKey($state)) === null) {
                throw new ApiException(
                    HttpStatus::BAD_REQUEST,
                    'OAUTH_STATE_INVALID',
                    'This sign-in attempt is invalid or has expired. Please try again.',
                );
            }

            // Exchange the code from the URL for the Google profile
            $googleUser = Socialite::driver('google')->stateless()->user();

            $member = $this->oauth->resolveGoogleMember($googleUser);
            $token = $this->oauth->issueToken($member);

            // The token goes in the #fragment, which browsers never send to servers or log
            return redirect()->away($frontendCallback . '#' . http_build_query([
                'token' => $token,
                'type' => 'GOOGLE_LOGIN_SUCCESS',
            ]));
        } catch (ApiException $e) {
            return redirect()->away($frontendCallback . '#' . http_build_query([
                'error' => $e->type,
                'message' => $e->getMessage(),
            ]));
        } catch (Throwable $e) {
            Log::error('Google OAuth callback failed', ['exception' => $e]);

            return redirect()->away($frontendCallback . '#' . http_build_query([
                'error' => 'OAUTH_FAILED',
                'message' => 'Google sign-in failed. Please try again.',
            ]));
        }
    }
}
