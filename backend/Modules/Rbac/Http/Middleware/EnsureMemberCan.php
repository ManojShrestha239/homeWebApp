<?php

declare(strict_types=1);

namespace Modules\Rbac\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Member\Models\Member;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes:
 *   ->middleware('member.can:inventory.products.delete')          needs this permission
 *   ->middleware('member.can:sales.orders.view|accounting.reports.view')   needs ANY of these
 */
final class EnsureMemberCan
{
    public function handle(Request $request, Closure $next, string $permissions): Response
    {
        $member = $request->user();

        // Defense in depth: the JWT middleware should already have set this
        if (! $member instanceof Member) {
            return ApiResponse::make(HttpStatus::UNAUTHORIZED, 'UNAUTHENTICATED', 'Please log in first.');
        }

        // "a|b" means the member needs at least one of the listed permissions
        $required = explode('|', $permissions);

        if (! $member->hasAnyPermission($required)) {
            // Don't list the required permission names in the reply. That would reveal the permission map.
            return ApiResponse::make(
                HttpStatus::FORBIDDEN,
                'FORBIDDEN',
                'You do not have permission to do that.',
            );
        }

        return $next($request);
    }
}
