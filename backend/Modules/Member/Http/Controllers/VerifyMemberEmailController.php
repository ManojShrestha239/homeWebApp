<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Member\Services\MemberEmailVerificationService;
use Shared\Exceptions\ApiException;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

/**
 * GET /api/v1/members/verify?token=...
 * Reads the token from the URL, asks the service to verify it, and replies with JSON.
 */
final class VerifyMemberEmailController
{
    public function __construct(
        private readonly MemberEmailVerificationService $verificationService,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        // query('token') reads ?token=... from the URL. It can be null or an array, so check it
        $token = $request->query('token');

        try {
            if (! is_string($token) || $token === '') {
                throw new ApiException(
                    HttpStatus::BAD_REQUEST,
                    'MISSING_VERIFICATION_TOKEN',
                    'The verification link is incomplete.',
                );
            }

            $this->verificationService->verify($token);
        } catch (ApiException $e) {
            // Known errors (invalid, expired, failed) become our JSON schema here
            return ApiResponse::make($e->status, $e->type, $e->getMessage(), $e->description);
        }

        // Success: the same reply whether the email was just verified or was already verified
        return ApiResponse::make(
            status: HttpStatus::OK,
            type: 'EMAIL_VERIFIED',
            message: 'Your email has been verified. You can now log in.',
        );
    }
}
