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
        return true;
    }

    public function view(User $user, StandardSection $section): bool
    {
        return $section->standard->is_active;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, StandardSection $section): bool
    {
        return false;
    }

    public function delete(User $user, StandardSection $section): bool
    {
        return false;
    }
}
