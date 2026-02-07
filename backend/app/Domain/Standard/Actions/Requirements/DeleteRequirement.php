<?php

declare(strict_types=1);

namespace App\Domain\Standard\Actions\Requirements;

use App\Domain\Standard\Models\StandardRequirement;

class DeleteRequirement
{
    public function execute(StandardRequirement $requirement): bool
    {
        return $requirement->delete();
    }
}
