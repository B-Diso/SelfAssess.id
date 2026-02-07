<?php

namespace App\Domain\Assessment\Actions\ActionPlans;

use App\Domain\Assessment\Models\AssessmentActionPlan;

class DeleteActionPlan
{
    public function handle(AssessmentActionPlan $actionPlan): void
    {
        $actionPlan->delete();
    }
}
