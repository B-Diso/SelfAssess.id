<?php

namespace Database\Seeders;

use App\Domain\Organization\Models\Organization;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
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
            Permission::create(['name' => $permission, 'guard_name' => 'api']);
        }

        // Create Master Organization
        $masterOrg = Organization::create([
            'name' => config('organization.master.name'),
            'description' => config('organization.master.description'),
            'is_active' => true,
        ]);

        // Create Roles

        // Super Admin Role (all permissions)
        $superAdminRole = Role::create(['name' => 'super_admin', 'guard_name' => 'api']);
        $superAdminRole->givePermissionTo(Permission::all());

        // Organization Admin Role
        $orgAdminRole = Role::create(['name' => 'organization_admin', 'guard_name' => 'api']);
        $orgAdminRole->givePermissionTo([
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
        $orgUserRole = Role::create(['name' => 'organization_user', 'guard_name' => 'api']);
        $orgUserRole->givePermissionTo([
            'view-users',
            'view-organizations',
            'view-roles',
            'view-assessments',
            'view-attachments',
        ]);

        // Create Super Admin User
        $superAdminEmail = 'superadmin@example.com';
        $superAdmin = User::create([
            'organization_id' => $masterOrg->id,
            'name' => 'Super Administrator',
            'email' => $superAdminEmail,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);
        $superAdmin->assignRole('super_admin');

        $this->command->info('✅ Roles and Permissions setup completed successfully!');
        $this->command->info('📧 Super Admin Email: '.$superAdminEmail);
        $this->command->info('🔑 Password: password');
        $this->command->warn('⚠️  Change the default password immediately after first login!');
    }
}
