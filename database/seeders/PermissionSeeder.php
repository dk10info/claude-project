<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Task permissions
            'view_tasks',
            'create_tasks',
            'update_tasks',
            'delete_tasks',
            'assign_tasks',
            'view_any_tasks',
            'update_any_tasks',
            'delete_any_tasks',

            // Employee permissions
            'view_employees',
            'create_employees',
            'update_employees',
            'delete_employees',
            'view_any_employees',
            'update_any_employees',
            'delete_any_employees',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole = Role::findByName('admin');
        $employeeRole = Role::findByName('employee');

        // Admin gets all permissions
        $adminRole->givePermissionTo(Permission::all());

        // Employee gets limited permissions
        $employeePermissions = [
            'view_tasks',
            'update_tasks', // Can update their own tasks
        ];

        $employeeRole->givePermissionTo($employeePermissions);
    }
}
