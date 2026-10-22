<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Member\Http\Requests\ResetPasswordRequest;
use Modules\Member\Services\MemberPasswordResetService;
use Shared\Exceptions\ApiException;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

/**
 * POST /api/v1/members/password/reset
 * Body: { token, password, password_confirmation }
 */
final class ResetPasswordController
{
    public function __construct(
        private readonly MemberPasswordResetService $resetService,
    ) {}

    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $this->resetService->resetPassword(
                (string) $request->validated('token'),
                (string) $request->validated('password'),
            );
        } catch (ApiException $e) {
            return ApiResponse::make($e->status, $e->type, $e->getMessage(), $e->description);
        }

        return ApiResponse::make(
            status: HttpStatus::OK,
            type: 'PASSWORD_RESET_SUCCESS',
            message: 'Your password has been changed. You can now log in.',
        );
    }
}
