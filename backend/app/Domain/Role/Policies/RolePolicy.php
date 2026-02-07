<?php

namespace App\Domain\Role\Policies;

use App\Domain\User\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin() || $user->isOrganizationAdmin();
    }

    public function view(User $user, Role $role): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('manage-roles') || $user->isSuperAdmin();
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('manage-roles') || $user->isSuperAdmin();
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('manage-roles') || $user->isSuperAdmin();
    }

    public function viewPermissions(User $user): bool
    {
        return $user->isSuperAdmin();
    }
}
