<?php

namespace Database\Factories;

use App\Domain\Standard\Models\StandardDomain;
use App\Domain\Standard\Models\StandardElement;
use App\Domain\Standard\Models\Standard;
use Illuminate\Database\Eloquent\Factories\Factory;

class StandardElementFactory extends Factory
{
    protected $model = StandardElement::class;

    public function definition(): array
    {
        return [
            'standard_id' => Standard::factory(),
            'parent_id' => StandardDomain::factory(),
            'code' => $this->faker->unique()->bothify('ELE-###'),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'order' => $this->faker->numberBetween(1, 100),
            'type' => 'element',
        ];
    }
}
