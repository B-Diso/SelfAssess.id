<?php

namespace App\Domain\Assessment\Actions\ActionPlans;

use App\Domain\Assessment\Models\AssessmentActionPlan;

class UpdateActionPlan
{
    public function handle(AssessmentActionPlan $actionPlan, array $data): AssessmentActionPlan
    {
        $actionPlan->fill($data);
        $actionPlan->save();

        return $actionPlan;
    }
}
