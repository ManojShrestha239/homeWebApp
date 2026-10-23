<?php

declare(strict_types=1);

namespace Modules\Rbac\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Rbac\Support\RbacCatalog;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates every permission and role. Safe to run more than once:
 * findOrCreate skips anything that already exists.
 */
final class RbacSeeder extends Seeder
{
    public function run(): void
    {
        // Clear Spatie's cache first, so we never read stale permissions while seeding
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (RbacCatalog::PERMISSIONS as $name) {
            Permission::findOrCreate($name, RbacCatalog::GUARD);
        }

        foreach (RbacCatalog::rolePermissions() as $roleName => $permissions) {
            $role = Role::findOrCreate($roleName, RbacCatalog::GUARD);

            // syncPermissions makes the role match the catalog exactly.
            // Re-running the seeder after editing the catalog updates the role.
            $role->syncPermissions($permissions);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
