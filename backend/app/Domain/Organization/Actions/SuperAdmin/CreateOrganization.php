<?php

namespace App\Domain\Organization\Actions\SuperAdmin;

use App\Domain\Organization\Models\Organization;

class CreateOrganization
{
    public function execute(array $data): Organization
    {
        return Organization::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);
    }
}
