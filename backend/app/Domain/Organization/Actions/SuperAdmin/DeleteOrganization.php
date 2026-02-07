<?php

namespace App\Domain\Organization\Actions\SuperAdmin;

use App\Domain\Organization\Models\Organization;

class DeleteOrganization
{
    public function execute(Organization $organization): bool
    {
        // Business rules are enforced in the model's boot method
        // - Cannot delete example organization
        // - Cannot delete organization with active members
        return $organization->delete();
    }
}
