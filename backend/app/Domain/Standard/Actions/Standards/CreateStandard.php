<?php

namespace App\Domain\Standard\Actions\Standards;

use App\Domain\Standard\Models\Standard;

class CreateStandard
{
    public function execute(array $data): Standard
    {
        $isActive = $data['isActive'] ?? $data['is_active'] ?? true;

        $dbData = [
            'name' => $data['name'],
            'version' => $data['version'],
            'type' => $data['type'] ?? 'internal',
            'description' => $data['description'] ?? null,
            'is_active' => $isActive,
        ];

        $standard = Standard::create($dbData);

        return $standard->fresh();
    }
}
