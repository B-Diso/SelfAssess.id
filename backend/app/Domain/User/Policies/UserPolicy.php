<?php

namespace App\Domain\User\Policies;

use App\Domain\User\Models\User;

class UserPolicy
{
    public function viewAny(User $actor): bool
    {
        return $actor->can('view-users');
    }

    public function view(User $actor, User $target): bool
    {
        return $actor->can('view-users') && $actor->canAccessOrganization((string) $target->organization_id);
    }

    public function create(User $actor): bool
    {
        return $actor->can('create-user');
    }

    public function update(User $actor, User $target): bool
    {
        if ($actor->id === $target->id) {
            return true;
        }
        return $actor->can('update-user') && $actor->canAccessOrganization((string) $target->organization_id);
    }

    public function delete(User $actor, User $target): bool
    {
        return $actor->can('delete-user') && $actor->canAccessOrganization((string) $target->organization_id);
    }

    public function assignRole(User $actor, User $target): bool
    {
        return $actor->can('assign-roles') && $actor->canAccessOrganization((string) $target->organization_id);
    }

    public function transfer(User $actor): bool
    {
        return $actor->can('transfer-user');
    }
}
