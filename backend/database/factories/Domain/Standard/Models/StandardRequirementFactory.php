<?php

namespace Database\Factories\Domain\Standard\Models;

use App\Domain\Standard\Models\MasterRequirement;
use App\Domain\Standard\Models\StandardRequirement;
use App\Domain\Standard\Models\StandardSection;
use Illuminate\Database\Eloquent\Factories\Factory;

class StandardRequirementFactory extends Factory
{
    protected $model = StandardRequirement::class;

    public function definition(): array
    {
        return [
            'standard_section_id' => StandardSection::factory(),
            'master_requirement_id' => MasterRequirement::factory(),
            'display_code' => $this->faker->unique()->bothify('STD-REQ-###'),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
