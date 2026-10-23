<?php

declare(strict_types=1);

namespace Modules\Rbac\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Shared\responses\ApiResponse;
use Shared\Utils\HttpStatus;

final class RbacDemoController
{
    public function __invoke(?string $id = null): JsonResponse
    {
        return ApiResponse::make(HttpStatus::OK, 'RBAC_DEMO_OK', 'Access granted.', null, ['id' => $id]);
    }
}
