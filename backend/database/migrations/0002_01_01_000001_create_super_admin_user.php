<?php

use App\Domain\Organization\Models\Organization;
use App\Domain\User\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get or create master organization
        $masterOrg = Organization::firstOrCreate(
            ['name' => config('organization.master.name')],
            [
                'description' => config('organization.master.description'),
                'is_active' => true,
            ]
        );

        // Create Super Admin user if doesn't exist
        $superAdminEmail = 'superadmin@' . config('organization.master.email_domain');
        $superAdmin = User::firstOrCreate(
            ['email' => $superAdminEmail],
            [
                'organization_id' => $masterOrg->id,
                'name' => 'Super Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Ensure Super Admin role is assigned
        if (!$superAdmin->hasRole('super_admin')) {
            $superAdmin->assignRole('super_admin');
        }

        // Output info to console
        if (app()->runningInConsole()) {
            echo "\n✅ Super Admin created successfully!\n";
            echo "📧 Email: " . $superAdminEmail . "\n";
            echo "🔑 Password: password\n";
            echo "⚠️  IMPORTANT: Change the default password before deploying to production!\n\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Find and delete Super Admin user
        $superAdminEmail = 'superadmin@' . config('organization.master.email_domain');
        $superAdmin = User::where('email', $superAdminEmail)->first();
        if ($superAdmin) {
            $superAdmin->delete();
        }

        // Find and delete master organization (only if no other users)
        $masterOrg = Organization::where('name', config('organization.master.name'))->first();
        if ($masterOrg && $masterOrg->users()->count() === 0) {
            $masterOrg->forceDelete(); // Force delete to bypass protections
        }
    }
};
