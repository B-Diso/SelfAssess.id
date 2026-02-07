<?php

declare(strict_types=1);

namespace App\Domain\Standard\Actions\Requirements;

use App\Domain\Standard\Models\StandardRequirement;

class CreateRequirement
{
    public function execute(array $data): StandardRequirement
    {
        return StandardRequirement::create($data);
    }
}
