<?php

namespace Database\Factories\Domain\Assessment\Models;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Models\AssessmentActionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentActionPlanFactory extends Factory
{
    protected $model = AssessmentActionPlan::class;

    public function definition(): array
    {
        return [
            'assessment_id' => Assessment::factory(),
            'action' => $this->faker->sentence,
            'due_date' => $this->faker->date(),
            'pic' => $this->faker->name,
            'status' => 'open',
            'progress_note' => $this->faker->sentence,
        ];
    }
}
