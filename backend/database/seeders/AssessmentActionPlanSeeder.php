<?php

namespace Database\Seeders;

use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Assessment\Models\AssessmentActionPlan;
use App\Domain\Assessment\Enums\AssessmentStatus;
use Illuminate\Database\Seeder;

class AssessmentActionPlanSeeder extends Seeder
{
    /**
     * Action plan title templates
     */
    private array $titleTemplates = [
        'Implement {requirement} controls',
        'Develop {requirement} documentation',
        'Establish {requirement} procedures',
        'Conduct {requirement} training',
        'Enhance {requirement} monitoring',
        'Address gaps in {requirement}',
        'Improve {requirement} compliance',
        'Strengthen {requirement} processes',
    ];

    /**
     * Action plan description templates
     */
    private array $actionPlanTemplates = [
        "Conduct gap analysis to identify missing controls and implement required measures. Update documentation and train relevant staff. Timeline: {timeline}. Owner: {department}.",
        "Develop and implement comprehensive procedures for {area}. Include regular monitoring and review mechanisms. Timeline: {timeline}. Responsible: {department}.",
        "Review current practices against requirements and implement necessary changes. Establish metrics for ongoing compliance. Timeline: {timeline}. Lead: {department}.",
        "Create formal documentation and establish review processes. Conduct training sessions for all stakeholders. Timeline: {timeline}. Department: {department}.",
        "Strengthen control environment through additional monitoring and testing. Implement automated controls where feasible. Timeline: {timeline}. Team: {department}.",
    ];

    /**
     * Timeline options
     */
    private array $timelines = [
        'Immediate',
        '1-2 weeks',
        '2-4 weeks',
        '1 month',
        '2 months',
        '1 quarter',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📋 Seeding assessment action plans...');
        $this->command->newLine();

        // Get non-compliant or partially compliant responses
        $responses = AssessmentResponse::whereIn('compliance_status', ['partially_compliant', 'non_compliant'])
            ->whereHas('assessment', function ($query) {
                // Only create action plans for active/reviewed assessments
                $query->whereIn('status', [
                    AssessmentStatus::ACTIVE->value,
                    AssessmentStatus::PENDING_REVIEW->value,
                    AssessmentStatus::REVIEWED->value,
                ]);
            })
            ->with(['assessment', 'requirement'])
            ->get();

        if ($responses->isEmpty()) {
            $this->command->warn('⚠️  No non-compliant responses found. Action plans require partially_compliant or non_compliant responses.');
            return;
        }

        $totalActionPlans = 0;
        $overdueCount = 0;
        $upcomingCount = 0;

        foreach ($responses as $response) {
            // 70% chance to create action plan for non-compliant/partially compliant responses
            if (rand(1, 100) > 70) {
                continue;
            }

            $actionPlan = $this->createActionPlan($response);
            $totalActionPlans++;

            if ($actionPlan->due_date->isPast()) {
                $overdueCount++;
            } else {
                $upcomingCount++;
            }

            $daysInfo = $actionPlan->due_date->isPast()
                ? (abs($actionPlan->due_date->diffInDays(now())) . ' days overdue')
                : ($actionPlan->due_date->diffInDays(now()) . ' days remaining');

            $this->command->info("  ✓ {$actionPlan->title} ({$daysInfo})");
        }

        $this->command->newLine();
        $this->command->info("✅ Total {$totalActionPlans} action plans created:");
        $this->command->info("   - Overdue: {$overdueCount}");
        $this->command->info("   - Upcoming: {$upcomingCount}");
    }

    /**
     * Create action plan for a response
     */
    private function createActionPlan(AssessmentResponse $response): AssessmentActionPlan
    {
        $requirementTitle = $response->requirement->title;
        $requirementCode = $response->requirement->display_code;

        // Generate title
        $titleTemplate = $this->titleTemplates[array_rand($this->titleTemplates)];
        $title = str_replace('{requirement}', $requirementCode, $titleTemplate);

        // Generate action plan description
        $actionPlanTemplate = $this->actionPlanTemplates[array_rand($this->actionPlanTemplates)];
        $timeline = $this->timelines[array_rand($this->timelines)];
        $department = $this->getDepartmentName();
        $area = $this->extractAreaFromRequirement($requirementTitle);

        $actionPlan = str_replace(
            ['{timeline}', '{department}', '{area}'],
            [$timeline, $department, $area],
            $actionPlanTemplate
        );

        // Calculate due date
        $dueDate = $this->calculateDueDate($response);

        // Get PIC (person in charge)
        $pic = $this->getRandomUser($response->assessment->organization_id);

        return AssessmentActionPlan::create([
            'assessment_id' => $response->assessment_id,
            'assessment_response_id' => $response->id,
            'title' => $title,
            'action_plan' => $actionPlan,
            'due_date' => $dueDate,
            'pic' => $pic,
        ]);
    }

    /**
     * Calculate due date for action plan
     */
    private function calculateDueDate(AssessmentResponse $response): \DateTime
    {
        $now = now();
        $responseDate = $response->updated_at;

        // Determine due date range
        $daysSinceResponse = $now->diffInDays($responseDate);

        // 30% overdue, 40% due soon (within 7 days), 30% future
        $rand = mt_rand(1, 100);

        if ($rand <= 30) {
            // Overdue: 1-30 days ago
            return $now->subDays(rand(1, 30));
        } elseif ($rand <= 70) {
            // Due soon: within next 7 days
            return $now->addDays(rand(1, 7));
        } else {
            // Future: 8-60 days from now
            return $now->addDays(rand(8, 60));
        }
    }

    /**
     * Get department name for PIC
     */
    private function getDepartmentName(): string
    {
        $departments = [
            'Quality Assurance',
            'IT Security',
            'Compliance',
            'Operations',
            'Risk Management',
            'Internal Audit',
            'Finance',
            'Human Resources',
        ];

        return $departments[array_rand($departments)];
    }

    /**
     * Extract area from requirement title
     */
    private function extractAreaFromRequirement(string $title): string
    {
        $areas = [
            'security controls',
            'quality management',
            'risk assessment',
            'documentation',
            'training and awareness',
            'monitoring and review',
            'incident management',
            'access control',
        ];

        return $areas[array_rand($areas)];
    }

    /**
     * Get random user name for PIC
     */
    private function getRandomUser(string $organizationId): ?string
    {
        $user = \App\Domain\User\Models\User::where('organization_id', $organizationId)
            ->inRandomOrder()
            ->first();

        return $user ? $user->name : null;
    }
}
