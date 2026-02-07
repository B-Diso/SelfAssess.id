<?php

declare(strict_types=1);

namespace App\Domain\Standard\Actions\Sections;

use App\Domain\Standard\Models\StandardSection;

class DeleteSection
{
    public function execute(StandardSection $section): bool
    {
        return $section->delete();
    }
}
