<?php

namespace Database\Factories\Domain\Assessment\Models;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Standard\Models\StandardRequirement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentResponseFactory extends Factory
{
    protected $model = AssessmentResponse::class;

    public function definition(): array
    {
        return [
            'assessment_id' => Assessment::factory(),
            'standard_requirement_id' => StandardRequirement::factory(),
            'status' => \App\Domain\Assessment\Enums\AssessmentResponseStatus::ACTIVE->value,
            'compliance_status' => $this->faker->randomElement(['non_compliant', 'partially_compliant', 'fully_compliant', 'not_applicable']),
            'comments' => $this->faker->sentence(),
        ];
    }
}
