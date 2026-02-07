<?php

declare(strict_types=1);

namespace App\Domain\Standard\Actions\Sections;

use App\Domain\Standard\Models\StandardSection;

class UpdateSection
{
    public function execute(StandardSection $section, array $data): StandardSection
    {
        $section->update($data);
        return $section->fresh();
    }
}
