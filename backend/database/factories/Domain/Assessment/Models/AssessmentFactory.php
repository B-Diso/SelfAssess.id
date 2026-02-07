<?php

namespace Database\Factories\Domain\Assessment\Models;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Organization\Models\Organization;
use App\Domain\Standard\Models\Standard;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssessmentFactory extends Factory
{
    protected $model = Assessment::class;

    public function definition(): array
    {
        $periodType = ['monthly', 'quarterly', 'semester', 'annual'][array_rand([0, 1, 2, 3])];
        $year = now()->year;
        $quarter = now()->quarter;

        $periodValue = match($periodType) {
            'monthly' => now()->format('F Y'),
            'quarterly' => "Q{$quarter} {$year}",
            'semester' => (now()->month <= 6) ? "Semester 1 {$year}" : "Semester 2 {$year}",
            'annual' => "Annual {$year}",
        };

        return [
            'organization_id' => Organization::factory(),
            'standard_id' => Standard::factory(),
            'name' => $this->faker->words(3, true) . ' Assessment',
            'period_value' => $periodValue,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => \App\Domain\Assessment\Enums\AssessmentStatus::ACTIVE->value,
        ];
    }
}
