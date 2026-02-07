<?php

namespace Database\Factories;

use App\Domain\Evidence\Models\EvidenceFile;
use App\Domain\Organization\Models\Organization;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Domain\Evidence\Models\EvidenceFile>
 */
class EvidenceFileFactory extends Factory
{
    protected $model = EvidenceFile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileName = $this->faker->word() . '.pdf';

        return [
            'organization_id' => Organization::factory(),
            'file_path' => 'evidence/' . $this->faker->year() . '/' . $this->faker->numberBetween(1, 12) . '/' . $this->faker->uuid() . '.pdf',
            'file_name' => $fileName,
            'file_type' => 'application/pdf',
            'file_size' => $this->faker->numberBetween(1000, 500000),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'tags' => [$this->faker->word(), $this->faker->word()],
            'uploaded_by' => User::factory(),
        ];
    }
}
