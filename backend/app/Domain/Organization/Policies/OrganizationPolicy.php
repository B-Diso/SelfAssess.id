<?php

namespace App\Domain\Organization\Policies;

use App\Domain\Organization\Models\Organization;
use App\Domain\User\Models\User;

class OrganizationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-organizations');
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->can('view-organizations') && $user->canAccessOrganization($organization->id);
    }

    public function create(User $user): bool
    {
        return $user->can('create-organization');
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->can('update-organization') && $user->canAccessOrganization($organization->id);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->can('delete-organization');
    }

    public function viewMembers(User $user, Organization $organization): bool
    {
        return $user->can('view-organization-members') && $user->canAccessOrganization($organization->id);
    }
}
