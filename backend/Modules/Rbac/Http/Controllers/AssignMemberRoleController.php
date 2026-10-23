<?php

declare(strict_types=1);

namespace Modules\Rbac\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Member\Models\Member;
use Modules\Rbac\Http\Requests\AssignMemberRoleRequest;
use Modules\Rbac\Services\MemberRoleService;
use Shared\Exceptions\ApiException;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

/**
 * POST /api/v1/members/{member}/role   body: { "role": "Accountant" }
 */
final class AssignMemberRoleController
{
    public function __construct(
        private readonly MemberRoleService $roles,
    ) {}

    public function __invoke(AssignMemberRoleRequest $request, Member $member): JsonResponse
    {
        $actor = $request->user();

        if (! $actor instanceof Member) {
            return ApiResponse::make(HttpStatus::UNAUTHORIZED, 'UNAUTHENTICATED', 'Please log in first.');
        }

        try {
            $this->roles->assign($actor, $member, (string) $request->validated('role'));
        } catch (ApiException $e) {
            return ApiResponse::make($e->status, $e->type, $e->getMessage(), $e->description);
        }

        return ApiResponse::make(
            status: HttpStatus::OK,
            type: 'MEMBER_ROLE_ASSIGNED',
            message: 'The role was updated.',
            data: [
                'member_id' => $member->id,
                'role' => (string) $request->validated('role'),
            ],
        );
    }
}
