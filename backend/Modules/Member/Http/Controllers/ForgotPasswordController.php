<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Member\Http\Requests\ForgotPasswordRequest;
use Modules\Member\Services\MemberPasswordResetService;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

/**
 * POST /api/v1/members/password/forgot
 * Always replies 200 with the same message, whether or not the email exists.
 */
final class ForgotPasswordController
{
    public function __construct(
        private readonly MemberPasswordResetService $resetService,
    ) {}

    public function __invoke(ForgotPasswordRequest $request): JsonResponse
    {
        $this->resetService->requestReset((string) $request->validated('email'));

        return ApiResponse::make(
            status: HttpStatus::OK,
            type: 'PASSWORD_RESET_REQUESTED',
            message: 'If an account exists for that email, we have sent a reset link.',
            description: 'The link expires in 30 minutes. Check your spam folder if it does not arrive.',
        );
    }
}
