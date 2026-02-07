<?php

namespace Database\Seeders;

use App\Domain\Organization\Models\Organization;
use App\Domain\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👥 Seeding sample users for departments...');

        // Skip master organization (Super Admin already created via migration)
        $organizations = Organization::where('name', '!=', config('organization.master.name'))
            ->get();

        $totalUsers = 0;

        foreach ($organizations as $org) {
            $domain = strtolower(str_replace(' ', '', $org->name)) . '.local';

            // Create Organization Admin
            $admin = User::firstOrCreate(
                ['email' => "admin@{$domain}"],
                [
                    'organization_id' => $org->id,
                    'name' => 'Admin - ' . $org->name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]
            );

            if ($admin->wasRecentlyCreated) {
                $admin->assignRole('organization_admin');
                $totalUsers++;
            }

            // Create 2 Organization Users
            for ($i = 1; $i <= 2; $i++) {
                $user = User::firstOrCreate(
                    ['email' => "user{$i}@{$domain}"],
                    [
                        'organization_id' => $org->id,
                        'name' => "User {$i} - {$org->name}",
                        'password' => Hash::make('password'),
                        'email_verified_at' => now(),
                    ]
                );

                if ($user->wasRecentlyCreated) {
                    $user->assignRole('organization_user');
                    $totalUsers++;
                }
            }
        }

        $this->command->info("✅ {$totalUsers} sample users created successfully!");
        $this->command->info('🔑 Default password for all users: password');
        $this->command->warn('⚠️  Change passwords before deploying to production!');
    }
}
