<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = ['hrm.view', 'hrm.manage', 'payroll.view', 'payroll.manage', 'reports.view', 'settings.manage'];
        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        Role::findOrCreate('Administrator', 'web')->syncPermissions($permissions);
        Role::findOrCreate('HR Manager', 'web')->syncPermissions(['hrm.view', 'hrm.manage', 'payroll.view', 'payroll.manage', 'reports.view']);
        Role::findOrCreate('Viewer', 'web')->syncPermissions(['hrm.view']);
    }
}
