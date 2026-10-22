<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Member\DTOs\LoginMemberDTO;
use Modules\Member\Http\Requests\LoginMemberRequest;
use Modules\Member\Http\Resources\MemberResource;
use Modules\Member\Services\MemberLoginService;
use Shared\Exceptions\ApiException;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

/**
 * POST /api/v1/members/login
 * Receives the email and password, asks the service for a token, returns JSON.
 */
final class LoginMemberController
{
    public function __construct(
        private readonly MemberLoginService $loginService,
    ) {}

    public function __invoke(LoginMemberRequest $request): JsonResponse
    {
        try {
            $dto = LoginMemberDTO::fromValidated($request->validated());
            $result = $this->loginService->login($dto);
        } catch (ApiException $e) {
            // Known errors (wrong password, unverified, server failure) become our schema here
            return ApiResponse::make($e->status, $e->type, $e->getMessage(), $e->description);
        }

        return ApiResponse::make(
            status: HttpStatus::OK,
            type: 'LOGIN_SUCCESS',
            message: 'Welcome back!',
            description: 'Send the access_token in the Authorization header as "Bearer <token>".',
            data: [
                'access_token' => $result['access_token'],
                'token_type' => $result['token_type'],
                'expires_in' => $result['expires_in'],
                'member' => MemberResource::make($result['member'])->resolve(),
            ],
        );
    }
}
