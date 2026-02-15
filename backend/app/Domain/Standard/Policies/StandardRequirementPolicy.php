<?php

declare(strict_types=1);

namespace App\Domain\Standard\Policies;

use App\Domain\Standard\Models\StandardRequirement;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StandardRequirementPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }    public function viewAny(User $user): bool
    {
        return $user->can('view-standards');
    }

    /**
     * Determine whether the user can view the requirement.
     */
    public function view(User $user, StandardRequirement $requirement): bool
    {
        return $user->can('view-standards') && $requirement->section->standard->is_active;
    }

    /**
     * Determine whether the user can create requirements.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the requirement.
     */
    public function update(User $user, StandardRequirement $requirement): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the requirement.
     */
    public function delete(User $user, StandardRequirement $requirement): bool
    {
        return false;
    }
}
