<?php

declare(strict_types=1);

namespace App\Domain\Standard\Actions\Sections;

use App\Domain\Standard\Models\StandardSection;

class CreateSection
{
    public function execute(array $data): StandardSection
    {
        return StandardSection::create($data);
    }
}
