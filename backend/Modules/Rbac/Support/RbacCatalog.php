<?php

declare(strict_types=1);

namespace Modules\Rbac\Support;

/**
 * The single source of truth for roles and permissions.
 * Permission names follow the pattern: module.resource.action
 */
final class RbacCatalog
{
    /** Must match the guard used by the members provider (see config/auth.php) */
    public const GUARD = 'api';

    public const ADMIN = 'Admin';
    public const INVENTORY_MANAGER = 'Inventory Manager';
    public const ACCOUNTANT = 'Accountant';
    public const SALES_REPRESENTATIVE = 'Sales Representative';

    /** Every permission the system knows about */
    public const PERMISSIONS = [
        'inventory.products.view',
        'inventory.products.create',
        'inventory.products.update',
        'inventory.products.delete',
        'inventory.stock.view',
        'inventory.stock.adjust',
        'sales.customers.view',
        'sales.customers.create',
        'sales.customers.update',
        'sales.orders.view',
        'sales.orders.create',
        'sales.orders.update',
        'sales.orders.cancel',
        'accounting.invoices.view',
        'accounting.invoices.create',
        'accounting.invoices.update',
        'accounting.reports.view',
        'accounting.reports.export',
        'members.roles.view',
        'members.roles.assign',
    ];

    /**
     * Which permissions each role receives.
     * @return array<string, list<string>>
     */
    public static function rolePermissions(): array
    {
        return [
            // Admin gets everything
            self::ADMIN => self::PERMISSIONS,

            self::INVENTORY_MANAGER => [
                'inventory.products.view',
                'inventory.products.create',
                'inventory.products.update',
                'inventory.products.delete',
                'inventory.stock.view',
                'inventory.stock.adjust',
                'sales.orders.view',          // read-only, to check stock against demand
            ],

            self::ACCOUNTANT => [
                'accounting.invoices.view',
                'accounting.invoices.create',
                'accounting.invoices.update',
                'accounting.reports.view',
                'accounting.reports.export',
                'sales.orders.view',
                'sales.customers.view',
                'inventory.products.view',
            ],

            self::SALES_REPRESENTATIVE => [
                'sales.customers.view',
                'sales.customers.create',
                'sales.customers.update',
                'sales.orders.view',
                'sales.orders.create',
                'sales.orders.update',
                'inventory.products.view',
                'inventory.stock.view',
            ],
        ];
    }

    /** @return list<string> */
    public static function roles(): array
    {
        return array_keys(self::rolePermissions());
    }
}
