<?php

namespace Database\Factories;

use App\Domain\Standard\Models\StandardDomain;
use App\Domain\Standard\Models\Standard;
use Illuminate\Database\Eloquent\Factories\Factory;

class StandardDomainFactory extends Factory
{
    protected $model = StandardDomain::class;

    public function definition(): array
    {
        return [
            'standard_id' => Standard::factory(),
            'code' => $this->faker->unique()->bothify('DOM-###'),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'order' => $this->faker->numberBetween(1, 100),
            'type' => 'domain',
        ];
    }
}
