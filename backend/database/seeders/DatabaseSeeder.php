<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Systematic seeding strategy for multi-tenant compliance platform:
     *
     * Phase 1 - Foundation (Sequential):
     *   1. Roles & Permissions (via migration)
     *   2. Standards (GIAS, ISO 9001, ISO 27001, ISO 14001, SOC 2)
     *   3. Organizations
     *   4. Users (per organization)
     *
     * Phase 2 - Assessment Data (Sequential):
     *   5. Assessments (various statuses: draft, active, reviewed, finished)
     *   6. Assessment Responses (different progress levels)
     *   7. Action Plans (for non-compliant items)
     *
     * Phase 3 - Attachments (Sequential):
     *   8. Attachments (link dummy PDFs to responses)
     */
    public function run(): void
    {
        $this->command->info('╔════════════════════════════════════════════════════════════╗');
        $this->command->info('║       SelfAssess.id - Comprehensive Data Seeding           ║');
        $this->command->info('║   Multi-Tenant Compliance Platform Demo Data Generator     ║');
        $this->command->info('╚════════════════════════════════════════════════════════════╝');
        $this->command->newLine();

        $startTime = microtime(true);

        // Disable foreign key checks for faster seeding
        if (config('database.default') === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        } else {
            DB::statement('SET CONSTRAINTS ALL DEFERRED;');
        }

        try {
            $this->seedPhase1();
            $this->seedPhase2();
            $this->seedPhase3();
        } finally {
            // Re-enable foreign key checks
            if (config('database.default') === 'mysql') {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->command->newLine();
        $this->command->info('╔════════════════════════════════════════════════════════════╗');
        $this->command->info('║                    🎉 Seeding Complete!                     ║');
        $this->command->info('╚════════════════════════════════════════════════════════════╝');
        $this->command->newLine();
        $this->command->info("⏱️  Total execution time: {$duration} seconds");
        $this->command->newLine();
        $this->displaySummary();
        $this->command->newLine();
        $this->command->warn('⚠️  This is development/demo data. Do NOT run in production!');
        $this->command->info('🔑 Default login: admin@compliancedepartment.local / password');
    }

    /**
     * Phase 1: Foundation - Standards, Organizations, Users
     */
    private function seedPhase1(): void
    {
        $this->command->info('┌─────────────────────────────────────────────────────────────┐');
        $this->command->info('│ Phase 1: Foundation Data                                   │');
        $this->command->info('└─────────────────────────────────────────────────────────────┘');
        $this->command->newLine();

        $this->command->info('⏳ Seeding Quality Standards...');
        $this->call(StandardSeeder::class);
        $this->call(IcofrStandardSeeder::class);
        $this->command->newLine();

        $this->command->info('⏳ Seeding Organizations...');
        $this->call(OrganizationSeeder::class);
        $this->command->newLine();

        $this->command->info('⏳ Seeding Users...');
        $this->call(UserSeeder::class);
        $this->command->newLine();

        $this->command->info('✅ Phase 1 complete');
        $this->command->newLine();
    }

    /**
     * Phase 2: Assessment Data - Assessments, Responses, Action Plans
     */
    private function seedPhase2(): void
    {
        $this->command->info('┌─────────────────────────────────────────────────────────────┐');
        $this->command->info('│ Phase 2: Assessment Data                                  │');
        $this->command->info('└─────────────────────────────────────────────────────────────┘');
        $this->command->newLine();

        $this->command->info('⏳ Seeding Assessments (5-8 per org, various statuses)...');
        $this->call(AssessmentSeeder::class);
        $this->command->newLine();

        $this->command->info('⏳ Seeding Assessment Responses (various progress levels)...');
        $this->call(AssessmentResponseSeeder::class);
        $this->command->newLine();

        $this->command->info('⏳ Seeding Action Plans (for non-compliant items)...');
        $this->call(AssessmentActionPlanSeeder::class);
        $this->command->newLine();

        $this->command->info('✅ Phase 2 complete');
        $this->command->newLine();
    }

    /**
     * Phase 3: Attachments - Link dummy PDFs to records
     */
    private function seedPhase3(): void
    {
        $this->command->info('┌─────────────────────────────────────────────────────────────┐');
        $this->command->info('│ Phase 3: Attachments                                      │');
        $this->command->info('└─────────────────────────────────────────────────────────────┘');
        $this->command->newLine();

        $this->command->info('⏳ Seeding Attachments (linking dummy PDFs)...');
        $this->call(AttachmentSeeder::class);
        $this->command->newLine();

        $this->command->info('✅ Phase 3 complete');
        $this->command->newLine();
    }

    /**
     * Display seeding summary statistics
     */
    private function displaySummary(): void
    {
        $this->command->info('📊 Seeding Summary:');
        $this->command->newLine();

        $stats = [
            'Standards' => \App\Domain\Standard\Models\Standard::count(),
            'Organizations' => \App\Domain\Organization\Models\Organization::where('name', '!=', config('organization.master.name'))->count(),
            'Users' => \App\Domain\User\Models\User::count(),
            'Assessments' => \App\Domain\Assessment\Models\Assessment::count(),
            'Assessment Responses' => \App\Domain\Assessment\Models\AssessmentResponse::count(),
            'Action Plans' => \App\Domain\Assessment\Models\AssessmentActionPlan::count(),
            'Attachments' => \App\Domain\Attachment\Models\Attachment::count(),
        ];

        foreach ($stats as $label => $count) {
            $this->command->info("   • {$label}: <info>{$count}</info>");
        }

        $this->command->newLine();

        // Display assessment status breakdown
        $assessmentStatuses = DB::table('assessments')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        if ($assessmentStatuses->isNotEmpty()) {
            $this->command->info('📋 Assessment Status Breakdown:');
            foreach ($assessmentStatuses as $status => $count) {
                $formattedStatus = str_replace('_', ' ', strtoupper($status));
                $this->command->info("   • {$formattedStatus}: {$count}");
            }
            $this->command->newLine();
        }

        // Display response status breakdown
        $responseStatuses = DB::table('assessment_responses')
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        if ($responseStatuses->isNotEmpty()) {
            $this->command->info('📝 Response Status Breakdown:');
            foreach ($responseStatuses as $status => $count) {
                $formattedStatus = str_replace('_', ' ', strtoupper($status));
                $this->command->info("   • {$formattedStatus}: {$count}");
            }
            $this->command->newLine();
        }

        // Display compliance breakdown
        $complianceStatuses = DB::table('assessment_responses')
            ->select('compliance_status', DB::raw('count(*) as count'))
            ->whereNotNull('compliance_status')
            ->groupBy('compliance_status')
            ->pluck('count', 'compliance_status');

        if ($complianceStatuses->isNotEmpty()) {
            $this->command->info('✓ Compliance Status Breakdown:');
            foreach ($complianceStatuses as $status => $count) {
                $formattedStatus = str_replace('_', ' ', ucwords($status));
                $icon = match($status) {
                    'fully_compliant' => '✅',
                    'partially_compliant' => '⚠️',
                    'non_compliant' => '❌',
                    'not_applicable' => '➖',
                    default => '•',
                };
                $this->command->info("   {$icon} {$formattedStatus}: {$count}");
            }
            $this->command->newLine();
        }
    }
}
