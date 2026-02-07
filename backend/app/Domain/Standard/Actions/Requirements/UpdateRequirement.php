<?php

declare(strict_types=1);

namespace App\Domain\Standard\Actions\Requirements;

use App\Domain\Standard\Models\StandardRequirement;

class UpdateRequirement
{
    public function execute(StandardRequirement $requirement, array $data): StandardRequirement
    {
        $requirement->update($data);
        return $requirement->fresh();
    }
}
