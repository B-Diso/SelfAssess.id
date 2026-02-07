<?php

namespace Database\Factories\Domain\Assessment\Models;

use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Evidence\Models\Evidence;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EvidenceFactory extends Factory
{
    protected $model = Evidence::class;

    public function definition(): array
    {
        return [
            'assessment_response_id' => AssessmentResponse::factory(),
            'file_path' => 'evidence/' . $this->faker->uuid . '.pdf',
            'file_name' => 'evidence.pdf',
            'uploaded_by' => User::factory(),
        ];
    }
}
