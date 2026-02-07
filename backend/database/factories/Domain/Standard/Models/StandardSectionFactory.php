<?php

namespace Database\Factories\Domain\Standard\Models;

use App\Domain\Standard\Models\Standard;
use App\Domain\Standard\Models\StandardSection;
use Illuminate\Database\Eloquent\Factories\Factory;

class StandardSectionFactory extends Factory
{
    protected $model = StandardSection::class;

    public function definition(): array
    {
        return [
            'standard_id' => Standard::factory(),
            'parent_id' => null,
            'type' => 'domain', // or 'principle', 'standard'
            'code' => $this->faker->unique()->bothify('SEC-###'),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'order' => $this->faker->numberBetween(1, 100),
        ];
    }
}
