<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Member\Http\Resources\MemberResource;
use Modules\Member\Models\Member;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

/**
 * GET /api/v1/members/me
 * Reads the member ID from the token and loads that member from the members table.
 */
final class MeController
{
    public function __invoke(): JsonResponse
    {
        try {
            // Reads "Authorization: Bearer <token>", checks the signature and expiry,
            // and returns the token's payload. It does NOT use the default auth guard.
            $payload = JWTAuth::parseToken()->getPayload();
        } catch (JWTException) {
            // Missing, expired, or fake token
            return $this->unauthenticated();
        }

        // "sub" (subject) is the member ID we stored when we created the token
        $member = Member::find($payload->get('sub'));

        if (! $member instanceof Member) {
            // Valid signature, but the member was deleted or the ID is wrong
            return $this->unauthenticated();
        }

        return ApiResponse::make(
            status: HttpStatus::OK,
            type: 'MEMBER_PROFILE',
            message: 'Member loaded.',
            data: MemberResource::make($member)->resolve(),
        );
    }

    private function unauthenticated(): JsonResponse
    {
        return ApiResponse::make(
            HttpStatus::UNAUTHORIZED,
            'UNAUTHENTICATED',
            'Please log in first.',
            'Your session is missing or has expired.',
        );
    }
}
