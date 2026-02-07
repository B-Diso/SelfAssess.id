<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // User permissions
            'create-user',
            'view-users',
            'update-user',
            'delete-user',
            'transfer-user',

            // Organization permissions
            'create-organization',
            'view-organizations',
            'update-organization',
            'delete-organization',
            'view-organization-members',

            // Role permissions
            'manage-roles',
            'assign-roles',
            'view-roles',
            'view-permissions',

            // Assessment permissions
            'view-assessments',
            'review-quality-assessments',

            // Standard permissions
            'view-standards',

            // Attachment permissions
            'view-attachments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission, 'guard_name' => 'api']
            );
        }

        // Create Roles

        // Super Admin Role (all permissions)
        $superAdminRole = Role::firstOrCreate(
            ['name' => 'super_admin', 'guard_name' => 'api']
        );
        $superAdminRole->syncPermissions(Permission::all());

        // Organization Admin Role
        $orgAdminRole = Role::firstOrCreate(
            ['name' => 'organization_admin', 'guard_name' => 'api']
        );
        $orgAdminRole->syncPermissions([
            'create-user',
            'view-users',
            'update-user',
            'delete-user',
            'view-organizations',
            'update-organization',
            'view-organization-members',
            'assign-roles',
            'view-roles',
            'view-permissions',
            'view-assessments',
            'review-quality-assessments',
            'view-standards',
            'view-attachments',
        ]);

        // Organization User Role
        $orgUserRole = Role::firstOrCreate(
            ['name' => 'organization_user', 'guard_name' => 'api']
        );
        $orgUserRole->syncPermissions([
            'view-users',
            'view-organizations',
            'view-roles',
            'view-assessments',
            'view-attachments',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove roles
        Role::whereIn('name', ['super_admin', 'organization_admin', 'organization_user'])->delete();

        // Remove permissions
        Permission::whereIn('name', [
            'create-user',
            'view-users',
            'update-user',
            'delete-user',
            'transfer-user',
            'create-organization',
            'view-organizations',
            'update-organization',
            'delete-organization',
            'view-organization-members',
            'manage-roles',
            'assign-roles',
            'view-roles',
            'view-permissions',
            'view-assessments',
            'review-quality-assessments',
            'view-standards',
            'view-attachments',
        ])->delete();

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
