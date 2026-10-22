<?php

declare(strict_types=1);

namespace Modules\Member\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Member\DTOs\RegisterMemberDTO;
use Modules\Member\Http\Requests\RegisterMemberRequest;
use Modules\Member\Http\Resources\MemberResource;
use Modules\Member\Services\MemberRegistrationService;
use Shared\Exceptions\ApiException;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

final class RegisterMemberController
{
    public function __construct(
        private readonly MemberRegistrationService $registrationService,
    ) {}

    public function __invoke(RegisterMemberRequest $request): JsonResponse
    {
        try {
            // 1. Turn the checked form data into a typed box
            $dto = RegisterMemberDTO::fromValidated($request->validated());

            // 2. Let the service do the real work
            $member = $this->registrationService->register($dto);
        } catch (ApiException $e) {
            // Our known errors (409 duplicate, 500 failure) become our JSON schema right here
            return ApiResponse::make(
                $e->status,
                $e->type,
                $e->getMessage(),
                $e->description,
            );
        }

        // 3. Reply with 201 Created
        return ApiResponse::make(
            status: HttpStatus::CREATED,
            type: 'MEMBER_REGISTERED',
            message: 'Account created. Please check your email to verify your address.',
            description: 'You must verify your email before you can log in.',
            data: MemberResource::make($member)->resolve(),
        );
    }
}
