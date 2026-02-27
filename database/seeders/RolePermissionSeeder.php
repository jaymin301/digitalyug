<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // Employee management
            'manage-employees', 'view-employees',
            // Lead management
            'create-leads', 'view-leads', 'edit-leads', 'delete-leads',
            // Project management
            'activate-projects', 'view-projects', 'manage-projects',
            // Concept management
            'assign-concepts', 'view-concepts', 'submit-concepts', 'approve-concepts',
            // Shoot management
            'manage-shoots', 'view-shoots', 'checkin-checkout',
            // Edit management
            'assign-edits', 'view-edits', 'update-edit-progress', 'approve-edits',
            // Reports
            'view-reports',
            // Notifications
            'view-notifications',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Create Roles & Assign Permissions ─────────────────
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $admin->syncPermissions(Permission::all());

        $manager = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'view-employees',
            'create-leads', 'view-leads', 'edit-leads',
            'activate-projects', 'view-projects', 'manage-projects',
            'assign-concepts', 'view-concepts', 'approve-concepts',
            'manage-shoots', 'view-shoots',
            'assign-edits', 'view-edits', 'approve-edits',
            'view-reports',
            'view-notifications',
        ]);

        $sales = Role::firstOrCreate(['name' => 'Sales Executive', 'guard_name' => 'web']);
        $sales->syncPermissions([
            'create-leads', 'view-leads', 'edit-leads',
            'view-projects',
            'view-notifications',
        ]);

        $writer = Role::firstOrCreate(['name' => 'Concept Writer', 'guard_name' => 'web']);
        $writer->syncPermissions([
            'view-concepts', 'submit-concepts',
            'view-projects',
            'view-notifications',
        ]);

        $shooter = Role::firstOrCreate(['name' => 'Shooting Person', 'guard_name' => 'web']);
        $shooter->syncPermissions([
            'view-shoots', 'checkin-checkout',
            'view-concepts',
            'view-projects',
            'view-notifications',
        ]);

        $editor = Role::firstOrCreate(['name' => 'Video Editor', 'guard_name' => 'web']);
        $editor->syncPermissions([
            'view-edits', 'update-edit-progress',
            'view-concepts',
            'view-projects',
            'view-notifications',
        ]);
    }
}
