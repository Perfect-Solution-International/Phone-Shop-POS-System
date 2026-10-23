<?php

namespace Database\Seeders;

use App\Models\Platform\SubscriptionPlan;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Super Admin
            'superadmin.view',
            'superadmin.manage',
            'plans.manage',
            'subscriptions.manage',
            'payments.verify',

            // POS & Sales
            'pos.view',
            'pos.sell',
            'pos.discount',
            'pos.refund',

            // Products, Phones & IMEI
            'products.view',
            'products.manage',
            'imei.view',
            'imei.manage',
            'imei.trade_in',

            // Purchases & Suppliers
            'purchases.view',
            'purchases.manage',
            'suppliers.manage',

            // Stock & Inventory
            'stock.view',
            'stock.adjust',
            'stock.transfer',

            // Repairs & Warranty
            'repairs.view',
            'repairs.manage',
            'repairs.assign',
            'parts.use',
            'warranties.view',
            'warranties.manage',
            'warranties.claim',

            // Customers & Finance
            'customers.view',
            'customers.manage',
            'expenses.view',
            'expenses.manage',
            'cashbook.view',
            'shifts.manage',
            'finances.view',

            // Administration & HRM
            'branches.view',
            'branches.manage',
            'users.manage',
            'roles.manage',
            'audit.view',
            'hrm.view',
            'hrm.manage',
            'payroll.view',
            'payroll.manage',
            'reports.view',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Super Administrator (Company Level)
        Role::findOrCreate('Super Administrator', 'web')->syncPermissions([
            'superadmin.view',
            'superadmin.manage',
            'plans.manage',
            'subscriptions.manage',
            'payments.verify',
            'settings.manage',
            'audit.view',
        ]);

        // Shop Administrator / Owner (All Shop Permissions)
        $shopPermissions = array_diff($permissions, [
            'superadmin.view',
            'superadmin.manage',
            'plans.manage',
            'subscriptions.manage',
            'payments.verify',
        ]);
        Role::findOrCreate('Administrator', 'web')->syncPermissions($shopPermissions);
        Role::findOrCreate('Shop Owner', 'web')->syncPermissions($shopPermissions);

        // Shop Manager
        Role::findOrCreate('Shop Manager', 'web')->syncPermissions([
            'pos.view', 'pos.sell', 'pos.discount', 'pos.refund',
            'products.view', 'products.manage', 'imei.view', 'imei.manage',
            'purchases.view', 'suppliers.manage', 'stock.view', 'stock.adjust',
            'repairs.view', 'repairs.manage', 'warranties.view', 'warranties.manage',
            'customers.view', 'customers.manage', 'expenses.view', 'cashbook.view',
            'shifts.manage', 'reports.view', 'hrm.view',
        ]);

        // Cashier
        Role::findOrCreate('Cashier', 'web')->syncPermissions([
            'pos.view', 'pos.sell', 'products.view', 'imei.view', 'customers.view', 'shifts.manage',
        ]);

        // Stock Manager
        Role::findOrCreate('Stock Manager', 'web')->syncPermissions([
            'products.view', 'products.manage', 'imei.view', 'imei.manage',
            'purchases.view', 'purchases.manage', 'suppliers.manage',
            'stock.view', 'stock.adjust', 'stock.transfer', 'parts.use',
        ]);

        // Technician
        Role::findOrCreate('Technician', 'web')->syncPermissions([
            'repairs.view', 'repairs.manage', 'parts.use', 'warranties.view', 'imei.view',
        ]);

        // Accountant
        Role::findOrCreate('Accountant', 'web')->syncPermissions([
            'expenses.view', 'expenses.manage', 'cashbook.view', 'finances.view',
            'reports.view', 'shifts.manage', 'payroll.view', 'payroll.manage',
        ]);

        // Customer (Customer Portal Access)
        Permission::findOrCreate('customer.portal', 'web');
        Role::findOrCreate('Customer', 'web')->syncPermissions([
            'customer.portal',
        ]);

        // Legacy roles for compatibility with existing tests
        Role::findOrCreate('HR Manager', 'web')->syncPermissions(['hrm.view', 'hrm.manage', 'payroll.view', 'payroll.manage', 'reports.view']);
        Role::findOrCreate('Viewer', 'web')->syncPermissions(['hrm.view']);

        // Default Subscription Plans
        SubscriptionPlan::firstOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter Plan',
                'description' => 'Ideal for single-counter phone shops with IMEI tracking and basic repairs.',
                'monthly_price' => 4500.00,
                'annual_price' => 48000.00,
                'trial_days' => 7,
                'max_branches' => 1,
                'max_staff' => 3,
                'max_products' => 500,
                'has_imei_tracking' => true,
                'has_repair_management' => true,
                'has_warranty_management' => true,
                'has_accounting_reports' => true,
                'has_multi_branch' => false,
                'has_customer_portal' => false,
                'is_active' => true,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['slug' => 'growth'],
            [
                'name' => 'Growth Plan',
                'description' => 'For growing phone retail shops with multiple technicians and multi-branch operations.',
                'monthly_price' => 8500.00,
                'annual_price' => 90000.00,
                'trial_days' => 7,
                'max_branches' => 3,
                'max_staff' => 10,
                'max_products' => null,
                'has_imei_tracking' => true,
                'has_repair_management' => true,
                'has_warranty_management' => true,
                'has_accounting_reports' => true,
                'has_multi_branch' => true,
                'has_customer_portal' => true,
                'is_active' => true,
            ]
        );

        SubscriptionPlan::firstOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise Plan',
                'description' => 'Full-scale franchise package with unlimited branches, advanced accounting, and priority SLA.',
                'monthly_price' => 16500.00,
                'annual_price' => 180000.00,
                'trial_days' => 7,
                'max_branches' => 99,
                'max_staff' => 99,
                'max_products' => null,
                'has_imei_tracking' => true,
                'has_repair_management' => true,
                'has_warranty_management' => true,
                'has_accounting_reports' => true,
                'has_multi_branch' => true,
                'has_customer_portal' => true,
                'is_active' => true,
            ]
        );
    }
}

