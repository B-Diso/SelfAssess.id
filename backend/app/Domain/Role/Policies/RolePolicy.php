<?php

declare(strict_types=1);

namespace App\Domain\Role\Policies;

use App\Domain\User\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Determine whether the user can view any roles.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-roles');
    }

    /**
     * Determine whether the user can view the role.
     */
    public function view(User $user): bool
    {
        return $user->can('view-roles');
    }

    /**
     * Determine whether the user can create roles.
     */
    public function create(User $user): bool
    {
        return $user->can('manage-roles');
    }

    /**
     * Determine whether the user can update the role.
     */
    public function update(User $user): bool
    {
        return $user->can('manage-roles');
    }

    /**
     * Determine whether the user can delete the role.
     */
    public function delete(User $user): bool
    {
        return $user->can('manage-roles');
    }

    /**
     * Determine whether the user can assign roles.
     */
    public function assign(User $user): bool
    {
        return $user->can('manage-roles');
    }
}
