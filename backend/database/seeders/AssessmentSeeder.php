<?php

namespace Database\Seeders;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Organization\Models\Organization;
use App\Domain\Standard\Models\Standard;
use App\Domain\Assessment\Enums\AssessmentStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssessmentSeeder extends Seeder
{
    /**
     * Assessment name templates
     */
    private array $nameTemplates = [
        '{year} {standard} Assessment',
        '{standard} Compliance Review - {period}',
        '{year} {standard} Internal Audit',
        'Q{quarter} {standard} Evaluation',
        '{standard} Certification Assessment - {year}',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📋 Seeding assessments across organizations...');
        $this->command->newLine();

        DB::transaction(function () {
            $organizations = Organization::where('name', '!=', config('organization.master.name'))
                ->get();

            $standards = Standard::all();

            if ($organizations->isEmpty() || $standards->isEmpty()) {
                $this->command->warn('⚠️  No organizations or standards found. Please run StandardSeeder and OrganizationSeeder first.');
                return;
            }

            $totalAssessments = 0;
            $assessmentsPerOrg = 5; // Create 5-8 assessments per organization
            $standardsPerOrg = 3; // Use 3 different standards per organization

            foreach ($organizations as $org) {
                $this->command->info("Organization: {$org->name}");

                // Select random standards for this organization
                $orgStandards = $standards->random(min($standardsPerOrg, $standards->count()));

                foreach ($orgStandards as $standard) {
                    // Create assessments with different statuses
                    $statuses = $this->getDistributionOfStatuses();
                    $countPerStatus = floor($assessmentsPerOrg / count($statuses));

                    foreach ($statuses as $status) {
                        // Add some randomness to the count
                        $count = $countPerStatus + (rand(0, 1) ? 1 : 0);

                        for ($i = 0; $i < $count; $i++) {
                            $assessment = $this->createAssessment($org, $standard, $status);
                            $totalAssessments++;

                            $this->command->info("  ✓ Created: {$assessment->name} [{$status->value}]");
                        }
                    }
                }

                $this->command->newLine();
            }

            $this->command->info("✅ Total {$totalAssessments} assessments created!");
            $this->command->newLine();
        });
    }

    /**
     * Create a single assessment
     */
    private function createAssessment(Organization $org, Standard $standard, AssessmentStatus $status): Assessment
    {
        $nameTemplate = $this->nameTemplates[array_rand($this->nameTemplates)];

        // Generate assessment name and period value
        $year = now()->year;
        $quarter = now()->quarter;
        $periodType = ['monthly', 'quarterly', 'semester', 'annual'][array_rand([0, 1, 2, 3])];

        // Generate period value based on period type
        $periodValue = match($periodType) {
            'monthly' => now()->format('F Y'), // "April 2025"
            'quarterly' => "Q{$quarter} {$year}", // "Q1 2025"
            'semester' => (now()->month <= 6) ? "Semester 1 {$year}" : "Semester 2 {$year}", // "Semester 1 2025"
            'annual' => "Annual {$year}", // "Annual 2025"
        };

        // For name generation, use a shorter period format
        $period = match($periodType) {
            'monthly' => now()->format('F Y'),
            'quarterly' => "Q{$quarter} {$year}",
            'semester' => (now()->month <= 6) ? "H1 {$year}" : "H2 {$year}",
            'annual' => "FY {$year}",
        };

        $name = str_replace(
            ['{year}', '{standard}', '{period}', '{quarter}'],
            [$year, $standard->name, $period, $quarter],
            $nameTemplate
        );

        // Calculate dates based on status
        $startDate = $this->calculateStartDate($status);
        $endDate = $this->calculateEndDate($status, $startDate);

        // Use new Assessment() + save() to trigger model validation
        // This ensures the boot() validation runs when saving
        $assessment = new Assessment([
            'organization_id' => $org->id,
            'standard_id' => $standard->id,
            'name' => $name,
            'period_value' => $periodValue,
            'status' => $status->value,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        $assessment->save(); // Triggers validation in boot() method

        // Create workflow logs for historical assessment
        $this->createWorkflowLogs($assessment, $status);

        return $assessment;
    }

    /**
     * Calculate start date based on assessment status
     */
    private function calculateStartDate(AssessmentStatus $status): \DateTime
    {
        return match($status) {
            AssessmentStatus::DRAFT => now()->subDays(rand(5, 15)),
            AssessmentStatus::ACTIVE => now()->subDays(rand(10, 30)),
            AssessmentStatus::PENDING_REVIEW => now()->subDays(rand(20, 45)),
            AssessmentStatus::REVIEWED => now()->subDays(rand(35, 60)),
            AssessmentStatus::PENDING_FINISH => now()->subDays(rand(50, 75)),
            AssessmentStatus::FINISHED => now()->subDays(rand(70, 120)),
            AssessmentStatus::REJECTED => now()->subDays(rand(25, 50)),
            AssessmentStatus::CANCELLED => now()->subDays(rand(30, 90)),
        };
    }

    /**
     * Calculate end date based on assessment status
     */
    private function calculateEndDate(AssessmentStatus $status, \DateTime $startDate): \DateTime
    {
        $durationInDays = match($status) {
            AssessmentStatus::DRAFT => rand(30, 60),
            AssessmentStatus::ACTIVE => rand(45, 90),
            AssessmentStatus::PENDING_REVIEW => rand(60, 90),
            AssessmentStatus::REVIEWED => rand(75, 100),
            AssessmentStatus::PENDING_FINISH => rand(90, 120),
            AssessmentStatus::FINISHED => rand(90, 120),
            AssessmentStatus::REJECTED => rand(45, 90),
            AssessmentStatus::CANCELLED => rand(30, 60),
        };

        return (clone $startDate)->addDays($durationInDays);
    }

    /**
     * Get distribution of assessment statuses
     */
    private function getDistributionOfStatuses(): array
    {
        return [
            AssessmentStatus::ACTIVE,          // Currently in progress
            AssessmentStatus::ACTIVE,          // Multiple active assessments
            AssessmentStatus::PENDING_REVIEW,  // Submitted but awaiting review
            AssessmentStatus::REVIEWED,        // Completed review
            AssessmentStatus::FINISHED,        // Historical completed
            AssessmentStatus::PENDING_FINISH,  // Waiting for finalization
            AssessmentStatus::REJECTED,        // Returned for corrections
        ];
    }

    /**
     * Create workflow logs for assessment
     */
    private function createWorkflowLogs(Assessment $assessment, AssessmentStatus $currentStatus): void
    {
        $logs = [];
        $systemUserId = $this->getSystemUserId();

        // All assessments start with draft
        $logs[] = [
            'from_status' => null,
            'to_status' => AssessmentStatus::DRAFT->value,
            'transitioned_at' => $assessment->start_date,
            'transitioned_by' => $systemUserId, // System created (using Super Admin)
            'notes' => 'Assessment created',
        ];

        // Determine workflow path based on current status
        $workflowPath = $this->getWorkflowPath($currentStatus);
        $daysBetweenTransitions = max(1, intval($assessment->end_date->diffInDays($assessment->start_date) / count($workflowPath)));

        $currentDate = clone $assessment->start_date;

        foreach ($workflowPath as $status) {
            $currentDate->addDays(rand(1, $daysBetweenTransitions + 5));

            $logs[] = [
                'from_status' => $logs[count($logs) - 1]['to_status'],
                'to_status' => $status->value,
                'transitioned_at' => $currentDate,
                'transitioned_by' => $this->getRandomUserId($assessment->organization_id),
                'notes' => $this->getTransitionNote($status),
            ];
        }

        // Bulk insert logs
        DB::table('assessment_workflow_logs')->insert(array_map(function ($log) use ($assessment) {
            return [
                'id' => \Illuminate\Support\Str::uuid7()->toString(),
                'loggable_type' => Assessment::class,
                'loggable_id' => $assessment->id,
                'from_status' => $log['from_status'],
                'to_status' => $log['to_status'],
                'user_id' => $log['transitioned_by'],
                'note' => $log['notes'],
                'created_at' => $log['transitioned_at'],
                'updated_at' => $log['transitioned_at'],
            ];
        }, $logs));
    }

    /**
     * Get workflow path to current status
     */
    private function getWorkflowPath(AssessmentStatus $currentStatus): array
    {
        return match($currentStatus) {
            AssessmentStatus::DRAFT => [],
            AssessmentStatus::ACTIVE => [AssessmentStatus::ACTIVE],
            AssessmentStatus::PENDING_REVIEW => [AssessmentStatus::ACTIVE, AssessmentStatus::PENDING_REVIEW],
            AssessmentStatus::REVIEWED => [AssessmentStatus::ACTIVE, AssessmentStatus::PENDING_REVIEW, AssessmentStatus::REVIEWED],
            AssessmentStatus::PENDING_FINISH => [AssessmentStatus::ACTIVE, AssessmentStatus::PENDING_REVIEW, AssessmentStatus::REVIEWED, AssessmentStatus::PENDING_FINISH],
            AssessmentStatus::FINISHED => [AssessmentStatus::ACTIVE, AssessmentStatus::PENDING_REVIEW, AssessmentStatus::REVIEWED, AssessmentStatus::PENDING_FINISH, AssessmentStatus::FINISHED],
            AssessmentStatus::REJECTED => [AssessmentStatus::ACTIVE, AssessmentStatus::PENDING_REVIEW, AssessmentStatus::REJECTED],
            AssessmentStatus::CANCELLED => [AssessmentStatus::ACTIVE, AssessmentStatus::CANCELLED],
        };
    }

    /**
     * Get transition note
     */
    private function getTransitionNote(AssessmentStatus $status): string
    {
        return match($status) {
            AssessmentStatus::ACTIVE => 'Assessment activated, team can now input responses',
            AssessmentStatus::PENDING_REVIEW => 'Submitted for organizational review',
            AssessmentStatus::REVIEWED => 'Review completed, all requirements approved',
            AssessmentStatus::PENDING_FINISH => 'Request for final approval submitted',
            AssessmentStatus::FINISHED => 'Assessment finalized and archived',
            AssessmentStatus::REJECTED => 'Returned for corrections and updates',
            AssessmentStatus::CANCELLED => 'Assessment cancelled',
            default => 'Status updated',
        };
    }

    /**
     * Get system user ID (Super Admin)
     */
    private function getSystemUserId(): ?string
    {
        return \App\Domain\User\Models\User::whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        })->first()?->id;
    }

    /**
     * Get random user ID from organization
     */
    private function getRandomUserId(string $organizationId): ?string
    {
        return \App\Domain\User\Models\User::where('organization_id', $organizationId)
            ->inRandomOrder()
            ->first()?->id;
    }
}
