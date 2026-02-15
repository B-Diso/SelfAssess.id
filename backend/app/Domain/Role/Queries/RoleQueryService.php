<?php

namespace App\Domain\Role\Queries;

use App\Domain\User\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleQueryService
{
    /**
     * Get roles based on user's access level
     * Super admins see all roles, others see organization-level roles only
     */
    public function getRolesForUser(User $user): Collection
    {
        if ($user->isSuperAdmin()) {
            return Role::with('permissions')->get();
        }

        // Organization users can only see organization-level roles (not super_admin)
        return Role::with('permissions')
            ->where('is_system', true)
            ->where('name', '!=', 'super_admin')
            ->get();
    }

    /**
     * Get organization-level roles only (excluding super_admin)
     */
    public function getOrganizationRoles(): Collection
    {
        return Role::with('permissions')
            ->where('is_system', true)
            ->where('name', '!=', 'super_admin')
            ->get();
    }

    /**
     * Get all system roles
     */
    public function getSystemRoles(): Collection
    {
        return Role::with('permissions')
            ->where('is_system', true)
            ->get();
    }

    /**
     * Get all custom (non-system) roles
     */
    public function getCustomRoles(): Collection
    {
        return Role::with('permissions')
            ->where('is_system', false)
            ->get();
    }

    /**
     * Get all permissions
     */
    public function getAllPermissions(): Collection
    {
        return Permission::all();
    }
}
