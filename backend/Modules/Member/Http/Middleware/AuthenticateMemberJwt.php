<?php

declare(strict_types=1);

namespace Modules\Member\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Member\Models\Member;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks "Authorization: Bearer <token>" and attaches the member to the request.
 * Missing, expired, or fake tokens get a 401 in our JSON schema.
 */
final class AuthenticateMemberJwt
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Verifies the signature and expiry, then returns the payload
            $payload = JWTAuth::parseToken()->getPayload();
        } catch (JWTException) {
            return $this->unauthenticated();
        }

        // "sub" is the member ID stored in the token when it was created
        $member = Member::find($payload->get('sub'));

        if (! $member instanceof Member) {
            return $this->unauthenticated();
        }

        // Let the rest of the request call $request->user() and get this member
        $request->setUserResolver(static fn(): Member => $member);

        return $next($request);
    }

    private function unauthenticated(): Response
    {
        return ApiResponse::make(
            HttpStatus::UNAUTHORIZED,
            'UNAUTHENTICATED',
            'Please log in first.',
        );
    }
}
