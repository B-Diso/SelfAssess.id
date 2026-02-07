<?php

declare(strict_types=1);

namespace App\Domain\Standard\Actions\Standards;

use App\Domain\Standard\Models\Standard;

class GetStandardTree
{
    /**
     * Get the full standards tree in a flat response.
     */
    public function execute(Standard $standard): array
    {
        // Fetch all sections with their requirements
        $allSections = $standard->allSections()
            ->with(['requirements'])
            ->orderBy('level')
            ->orderBy('code')
            ->get();

        $flatTree = [];

        foreach ($allSections as $section) {
            // Add the section itself
            $flatTree[] = $section;

            // Add its requirements immediately after the section
            // StandardSection model has a 'requirements' HasMany relationship
            foreach ($section->requirements as $requirement) {
                $flatTree[] = $requirement;
            }
        }

        return $flatTree;
    }
}
