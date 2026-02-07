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
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, StandardRequirement $requirement): bool
    {
        return $requirement->section->standard->is_active;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, StandardRequirement $requirement): bool
    {
        return false;
    }

    public function delete(User $user, StandardRequirement $requirement): bool
    {
        return false;
    }
}
