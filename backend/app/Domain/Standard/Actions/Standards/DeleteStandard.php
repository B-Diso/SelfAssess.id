<?php

namespace App\Domain\Standard\Actions\Standards;

use App\Domain\Standard\Models\Standard;

class DeleteStandard
{
    public function execute(Standard $standard): void
    {
        $standard->delete();
    }
}
