<?php

namespace Database\Factories\Domain\Standard\Models;

use App\Domain\Standard\Models\Standard;
use Illuminate\Database\Eloquent\Factories\Factory;

class StandardFactory extends Factory
{
    protected $model = Standard::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'version' => '1.0',
            'type' => 'global_iia_standards_2024',
            'description' => $this->faker->sentence(),
            'is_active' => true,
        ];
    }
}
