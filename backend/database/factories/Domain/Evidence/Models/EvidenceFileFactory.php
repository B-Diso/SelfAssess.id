<?php

namespace Database\Factories\Domain\Evidence\Models;

use App\Domain\Evidence\Models\EvidenceFile;
use App\Domain\Organization\Models\Organization;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EvidenceFileFactory extends Factory
{
    protected $model = EvidenceFile::class;

    public function definition(): array
    {
        return [
            'organization_id' => Organization::factory(),
            'file_path' => 'evidence/' . Str::uuid()->toString() . '.pdf',
            'file_name' => $this->faker->unique()->slug() . '.pdf',
            'file_type' => 'pdf',
            'file_size' => $this->faker->numberBetween(1000, 1000000),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'tags' => ['compliance', 'policy'],
            'uploaded_by' => User::factory(),
        ];
    }

    public function withTags(array $tags): static
    {
        return $this->state(fn (array $attributes) => [
            'tags' => $tags,
        ]);
    }

    public function forOrganization(Organization $organization): static
    {
        return $this->state(fn (array $attributes) => [
            'organization_id' => $organization->id,
        ]);
    }
}
