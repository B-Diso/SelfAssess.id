<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ResetDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reset-demo-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset demo database and clean up storage files (for demo environment only)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->warn('⚠️  WARNING: This will DELETE ALL DATA and reset the database!');
        $this->warn('This command is intended for DEMO environments ONLY.');

        if (! $this->confirm('Do you want to proceed?')) {
            $this->info('Operation cancelled.');
            return self::SUCCESS;
        }

        $this->info('Starting demo data reset...');
        $this->newLine();

        // Step 1: Clear and cache config
        $this->info('1. Clearing application cache...');
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        $this->info('   ✓ Cache cleared');

        // Step 2: Reset database with fresh migration and seed
        $this->info('2. Resetting database (migrate:fresh --seed)...');
        $exitCode = Artisan::call('migrate:fresh', [
            '--seed' => true,
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            $this->error('   ✗ Database reset failed');
            return self::FAILURE;
        }

        $this->info('   ✓ Database reset and seeded successfully');

        // Step 3: Clean up storage files
        $this->info('3. Cleaning up storage files...');

        // Clean public disk (attachments, etc.)
        $publicDisk = Storage::disk('public');
        $deletedFiles = 0;

        if ($publicDisk->exists('uploads')) {
            $files = $publicDisk->allFiles('uploads');

            foreach ($files as $file) {
                if ($publicDisk->delete($file)) {
                    $deletedFiles++;
                }
            }

            $this->info("   ✓ Deleted {$deletedFiles} files from public/uploads");
        } else {
            $this->info('   ✓ No uploads directory found');
        }

        // Clean app storage (logs, cache, etc.)
        $this->info('4. Cleaning up application storage...');

        $directoriesToDelete = [
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        foreach ($directoriesToDelete as $directory) {
            if (File::exists($directory)) {
                File::deleteDirectory($directory);
                $this->info("   ✓ Cleaned: {$directory}");
            }
        }

        // Step 4: Re-optimize for production
        $this->info('5. Optimizing application...');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
        $this->info('   ✓ Application optimized');

        $this->newLine();
        $this->info('✅ Demo data reset completed successfully!');
        $this->warn('⚠️  Note: All data has been reset to initial seeded state.');

        return self::SUCCESS;
    }
}
