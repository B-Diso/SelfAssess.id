<?php

namespace App\Domain\Assessment\Actions\ActionPlans;

use App\Domain\Assessment\Models\AssessmentActionPlan;
use Illuminate\Support\Str;

class CreateActionPlan
{
    public function handle(array $data): AssessmentActionPlan
    {
        $data['id'] = $data['id'] ?? Str::uuid();
        return AssessmentActionPlan::create($data);
    }
}
