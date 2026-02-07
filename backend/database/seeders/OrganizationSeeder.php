<?php

namespace Database\Seeders;

use App\Domain\Organization\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * List of departments/organizations
     */
    private array $organizations = [
        [
            'name' => 'Compliance Department',
            'description' => 'Responsible for regulatory compliance and audit management',
        ],
        [
            'name' => 'Quality Assurance Department',
            'description' => 'Testing and quality management division',
        ],
        [
            'name' => 'IT Operations Department',
            'description' => 'Information technology and infrastructure management',
        ],
        [
            'name' => 'Finance Department',
            'description' => 'Financial management and accounting',
        ],
        [
            'name' => 'Human Resources Department',
            'description' => 'Personnel management and development',
        ],
        [
            'name' => 'Marketing & Sales Department',
            'description' => 'Business development and customer relations',
        ],
        [
            'name' => 'Engineering Department',
            'description' => 'Software development and technical engineering',
        ],
        [
            'name' => 'Security Department',
            'description' => 'Information security and data protection',
        ],
        [
            'name' => 'Operations Department',
            'description' => 'Business operations and support services',
        ],
        [
            'name' => 'Research & Development Department',
            'description' => 'Product innovation and research',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🏢 Seeding departments...');

        foreach ($this->organizations as $org) {
            Organization::firstOrCreate(
                ['name' => $org['name']],
                [
                    'description' => $org['description'],
                    'is_active' => true,
                    'parent_id' => null,
                ]
            );
        }

        $this->command->info('✅ ' . count($this->organizations) . ' departments seeded successfully!');
    }
}
