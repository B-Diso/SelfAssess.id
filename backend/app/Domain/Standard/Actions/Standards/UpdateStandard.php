<?php

namespace App\Domain\Standard\Actions\Standards;

use App\Domain\Standard\Models\Standard;

class UpdateStandard
{
    public function execute(Standard $standard, array $data): Standard
    {
        // Map camelCase to snake_case for database
        $dbData = [];

        if (isset($data['name'])) $dbData['name'] = $data['name'];
        if (isset($data['version'])) $dbData['version'] = $data['version'];
        if (isset($data['type'])) $dbData['type'] = $data['type'];
        if (isset($data['description'])) $dbData['description'] = $data['description'];
        if (isset($data['isActive']) || isset($data['is_active'])) {
            $dbData['is_active'] = $data['isActive'] ?? $data['is_active'];
        }

        $standard->update($dbData);

        return $standard->fresh();
    }
}
