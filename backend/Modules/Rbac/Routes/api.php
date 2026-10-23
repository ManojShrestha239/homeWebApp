<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Rbac\Http\Controllers\AssignMemberRoleController;
use Modules\Rbac\Http\Controllers\RbacDemoController;

// Every route in this group needs a valid JWT first
Route::middleware('member.jwt')->group(function (): void {

    // Demo routes: replace with your real module routes
    Route::get('/rbac-demo/products', RbacDemoController::class)
        ->middleware('member.can:inventory.products.view');

    Route::delete('/rbac-demo/products/{id}', RbacDemoController::class)
        ->middleware('member.can:inventory.products.delete');

    // Only members with members.roles.assign (by default, Admin) can change roles
    Route::post('/members/{member}/role', AssignMemberRoleController::class)
        ->middleware('member.can:members.roles.assign');
});
