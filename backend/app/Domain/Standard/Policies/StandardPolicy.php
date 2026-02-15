<?php

declare(strict_types=1);

namespace App\Domain\Standard\Policies;

use App\Domain\Standard\Models\Standard;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StandardPolicy
{
    use HandlesAuthorization;

    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-standards');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Standard $standard): bool
    {
        return $user->can('view-standards') && $standard->is_active;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false; // Handled by before() for SuperAdmin
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Standard $standard): bool
    {
        return false; // Handled by before() for SuperAdmin
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Standard $standard): bool
    {
        return false; // Handled by before() for SuperAdmin
    }
}
