<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add is_system column to roles table
        Schema::table('roles', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('guard_name');
            $table->index('is_system');
        });

        // Mark existing system roles
        Role::whereIn('name', ['super_admin', 'organization_admin', 'organization_user'])
            ->update(['is_system' => true]);

        if (app()->runningInConsole()) {
            echo "\n✅ Added is_system column to roles table\n";
            echo "✅ Marked 3 system roles (super_admin, organization_admin, organization_user)\n\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropIndex(['is_system']);
            $table->dropColumn('is_system');
        });
    }
};
