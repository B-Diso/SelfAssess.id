<?php

declare(strict_types=1);

namespace App\Domain\Standard\Policies;

use App\Domain\Standard\Models\StandardSection;
use App\Domain\User\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StandardSectionPolicy
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
        return $user->can('view-standards');
    }

    /**
     * Determine whether the user can view the section.
     */
    public function view(User $user, StandardSection $section): bool
    {
        return $user->can('view-standards') && $section->standard->is_active;
    }

    /**
     * Determine whether the user can create sections.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the section.
     */
    public function update(User $user, StandardSection $section): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the section.
     */
    public function delete(User $user, StandardSection $section): bool
    {
        return false;
    }
}
