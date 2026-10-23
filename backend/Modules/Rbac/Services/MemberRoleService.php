<?php

declare(strict_types=1);

namespace Modules\Rbac\Services;

use Modules\Member\Models\Member;
use Modules\Rbac\Support\RbacCatalog;
use Shared\Exceptions\ApiException;
use Shared\Utils\HttpStatus;
use Spatie\Permission\Models\Role;

final class MemberRoleService
{
    public function assign(Member $actor, Member $target, string $roleName): void
    {
        // Rule 1: nobody changes their own role. This blocks self-promotion.
        if ($actor->is($target)) {
            throw new ApiException(
                HttpStatus::FORBIDDEN,
                'CANNOT_CHANGE_OWN_ROLE',
                'You cannot change your own role.',
            );
        }

        // Rule 2: only an Admin can grant Admin. This matters if a future role gets assign rights.
        if ($roleName === RbacCatalog::ADMIN && ! $actor->hasRole(RbacCatalog::ADMIN, RbacCatalog::GUARD)) {
            throw new ApiException(
                HttpStatus::FORBIDDEN,
                'ROLE_ESCALATION_DENIED',
                'Only an Admin can grant the Admin role.',
            );
        }

        // Rule 3: the role comes from the catalog, and the guard is explicit
        $role = Role::findByName($roleName, RbacCatalog::GUARD);

        // syncRoles replaces the member's roles with exactly this one
        $target->syncRoles([$role]);
    }
}
