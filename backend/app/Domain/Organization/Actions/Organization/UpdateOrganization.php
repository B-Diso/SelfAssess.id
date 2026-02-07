<?php

namespace App\Domain\Organization\Actions\Organization;

use App\Domain\Organization\Models\Organization;
use App\Exceptions\Domain\InvariantViolationException;

class UpdateOrganization
{
    public function execute(Organization $organization, array $data): Organization
    {
        if ($organization->isMaster() && isset($data['name']) && $data['name'] !== $organization->name) {
            throw new InvariantViolationException('Cannot rename ' . config('organization.master.name') . ' organization');
        }
        
        $data['is_active'] = $data['isActive'];

        $organization->update($data);

        return $organization->fresh();
    }
}
