<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $perm = Permission::firstOrCreate(['name' => 'review-assessments', 'guard_name' => 'api']);

        // Give to super_admin and organization_admin so reviewer tidak harus super admin.
        Role::whereIn('name', ['super_admin', 'organization_admin'])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($perm));
    }

    public function down(): void
    {
        $perm = Permission::where('name', 'review-assessments')->first();
        if ($perm) {
            $perm->delete();
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
