<?php

namespace Database\Seeders;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Standard\Models\StandardRequirement;
use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Enums\AssessmentResponseStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssessmentResponseSeeder extends Seeder
{
    /**
     * Response comments templates - Expanded for variety
     */
    private array $commentTemplates = [
        "We have implemented this requirement and documented the process.",
        "Policy approved by management on {date}. Regular reviews scheduled.",
        "Control measures in place. Monthly monitoring conducted.",
        "Training completed for all relevant staff. Records maintained.",
        "Procedures established and communicated to stakeholders.",
        "Compliance verified through internal audit. Minor gaps identified.",
        "Partially compliant. Action plan in progress.",
        "Full compliance achieved. Evidence available for review.",
        "Requirement met. Documentation available in quality management system.",
        "Process implemented and monitored. Performance within acceptable range.",
        "Standard operating procedures updated and approved on {date}.",
        "Internal audit conducted on {date}. All criteria met.",
        "Risk assessment completed. Mitigation strategies implemented.",
        "Documented procedures maintained and reviewed quarterly.",
        "Staff training completed. Competency assessments passed.",
        "Monitoring mechanisms established. KPIs tracked monthly.",
        "Third-party audit completed in {date}. Full compliance achieved.",
        "Gap analysis performed. Improvement actions implemented.",
        "Control framework aligned with standard requirements.",
        "Management review conducted. Continued suitability confirmed.",
        "Process owners assigned. Responsibilities clearly defined.",
        "Corrective actions from previous audit closed out.",
        "Evidence maintained for last 12 months. Ready for review.",
        "Continuous improvement process active. Feedback incorporated.",
    ];

    /**
     * Compliance statuses with weights for realistic distribution
     */
    private array $complianceStatuses = [
        'fully_compliant',
        'partially_compliant',
        'non_compliant',
        'not_applicable',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📝 Seeding assessment responses...');
        $this->command->newLine();

        DB::transaction(function () {
            $assessments = Assessment::with('standard')->get();

            if ($assessments->isEmpty()) {
                $this->command->warn('⚠️  No assessments found. Please run AssessmentSeeder first.');
                return;
            }

            $totalResponses = 0;
            $totalAttachments = 0;

            foreach ($assessments as $assessment) {
                $this->command->info("Assessment: {$assessment->name}");

                // Get all requirements for this standard
                $requirements = StandardRequirement::whereHas('section', function ($query) use ($assessment) {
                    $query->where('standard_id', $assessment->standard_id);
                })->get();

                if ($requirements->isEmpty()) {
                    $this->command->warn("  ⚠️  No requirements found for standard");
                    continue;
                }

                // Determine response pattern based on assessment status
                $responsePattern = $this->getResponsePattern($assessment->status->value);
                $responses = [];

                foreach ($requirements as $requirement) {
                    $responseData = $this->generateResponseData($assessment, $requirement, $responsePattern);
                    $response = AssessmentResponse::create($responseData);
                    $responses[] = $response;
                    $totalResponses++;

                    // Create workflow log if response has status (pass the original status enum, not the value)
                    $statusEnum = $this->determineResponseStatusFromValue($responseData['status']);
                    if ($statusEnum && $statusEnum !== AssessmentResponseStatus::ACTIVE) {
                        $this->createResponseWorkflowLog($response, $statusEnum);
                    }
                }

                $completedCount = collect($responses)->filter(fn($r) => $r->status === AssessmentResponseStatus::REVIEWED)->count();
                $totalCount = count($responses);
                $progress = $totalCount > 0 ? round(($completedCount / $totalCount) * 100) : 0;

                $this->command->info("  ✓ Created {$totalCount} responses ({$progress}% reviewed)");
            }

            $this->command->newLine();
            $this->command->info("✅ Total {$totalResponses} responses created!");
        });
    }

    /**
     * Generate response data for a requirement
     */
    private function generateResponseData(Assessment $assessment, StandardRequirement $requirement, array $pattern): array
    {
        // Determine status based on pattern and probability
        $status = $this->determineResponseStatus($pattern);

        // Determine compliance status
        $complianceStatus = $this->determineComplianceStatus($status);

        // Generate comments
        $comments = $this->generateComments($status, $complianceStatus);

        return [
            'assessment_id' => $assessment->id,
            'standard_requirement_id' => $requirement->id,
            'status' => $status->value,
            'compliance_status' => $complianceStatus,
            'comments' => $comments,
            'created_at' => $this->calculateResponseDate($assessment, $status),
            'updated_at' => now(),
        ];
    }

    /**
     * Determine response status based on pattern
     */
    private function determineResponseStatus(array $pattern): AssessmentResponseStatus
    {
        $rand = mt_rand(1, 100);

        foreach ($pattern as $status => $probability) {
            if ($rand <= $probability) {
                return AssessmentResponseStatus::from($status);
            }
            $rand -= $probability;
        }

        return AssessmentResponseStatus::ACTIVE;
    }

    /**
     * Determine compliance status with realistic distribution
     *
     * For REVIEWED responses:
     * - 50% fully_compliant
     * - 30% partially_compliant (needs action plan)
     * - 15% not_applicable
     * - 5% non_compliant (needs action plan)
     *
     * For ACTIVE/PENDING_REVIEW:
     * - Random distribution
     */
    private function determineComplianceStatus(AssessmentResponseStatus $status): string
    {
        if ($status === AssessmentResponseStatus::REVIEWED) {
            // Weighted distribution for reviewed responses
            $rand = mt_rand(1, 100);

            if ($rand <= 50) {
                return 'fully_compliant';
            } elseif ($rand <= 80) {
                return 'partially_compliant';
            } elseif ($rand <= 95) {
                return 'not_applicable';
            } else {
                return 'non_compliant';
            }
        }

        // Random distribution for active/pending_review
        return $this->complianceStatuses[array_rand($this->complianceStatuses)];
    }

    /**
     * Generate response comments
     *
     * For REVIEWED responses: 90% have comments (required for review evidence)
     * For ACTIVE/PENDING_REVIEW: 30-50% have comments
     */
    private function generateComments(AssessmentResponseStatus $status, string $complianceStatus): ?string
    {
        if ($status === AssessmentResponseStatus::REVIEWED) {
            // Reviewed responses should have comments 90% of the time
            return (rand(1, 100) <= 90) ? $this->getRandomComment() : null;
        }

        // Active responses have 30% chance of comments
        if ($status === AssessmentResponseStatus::ACTIVE) {
            return (rand(1, 100) <= 30) ? $this->getRandomComment() : null;
        }

        // Pending review: 60% chance of comments
        return (rand(1, 100) <= 60) ? $this->getRandomComment() : null;
    }

    /**
     * Get random comment template
     */
    private function getRandomComment(): string
    {
        $template = $this->commentTemplates[array_rand($this->commentTemplates)];
        $date = now()->subDays(rand(30, 180))->format('F j, Y');

        return str_replace('{date}', $date, $template);
    }

    /**
     * Calculate response date based on assessment and response status
     */
    private function calculateResponseDate(Assessment $assessment, AssessmentResponseStatus $status): \DateTime
    {
        $daysSinceStart = now()->diffInDays($assessment->start_date);

        return match($status) {
            AssessmentResponseStatus::ACTIVE => $assessment->start_date->addDays(rand(0, intval($daysSinceStart * 0.3))),
            AssessmentResponseStatus::PENDING_REVIEW => $assessment->start_date->addDays(rand(intval($daysSinceStart * 0.3), intval($daysSinceStart * 0.7))),
            AssessmentResponseStatus::REVIEWED => $assessment->start_date->addDays(rand(intval($daysSinceStart * 0.5), $daysSinceStart)),
        };
    }

    /**
     * Get response pattern based on assessment status
     *
     * BUSINESS RULES:
     * - PENDING_REVIEW: ALL requirements must be REVIEWED (100%)
     * - REVIEWED: ALL requirements must be REVIEWED (100%)
     * - PENDING_FINISH: ALL requirements must be REVIEWED (100%)
     * - FINISHED: ALL requirements must be REVIEWED (100%)
     *
     * Variation comes from compliance status and comments, NOT response status.
     */
    private function getResponsePattern(string $assessmentStatus): array
    {
        return match($assessmentStatus) {
            AssessmentStatus::DRAFT->value => [
                'active' => 100,
            ],
            AssessmentStatus::ACTIVE->value => [
                'active' => 60,
                'pending_review' => 30,
                'reviewed' => 10,
            ],
            // BUSINESS RULE: Can only submit if ALL requirements are reviewed
            AssessmentStatus::PENDING_REVIEW->value => [
                'reviewed' => 100,
            ],
            // BUSINESS RULE: All requirements reviewed by org admin
            AssessmentStatus::REVIEWED->value => [
                'reviewed' => 100,
            ],
            // BUSINESS RULE: All requirements reviewed
            AssessmentStatus::PENDING_FINISH->value => [
                'reviewed' => 100,
            ],
            // BUSINESS RULE: All requirements reviewed
            AssessmentStatus::FINISHED->value => [
                'reviewed' => 100,
            ],
            // REJECTED: Returned for corrections, can have mixed statuses
            AssessmentStatus::REJECTED->value => [
                'active' => 40,
                'pending_review' => 40,
                'reviewed' => 20,
            ],
            // CANCELLED: Incomplete, can have mixed statuses
            AssessmentStatus::CANCELLED->value => [
                'active' => 70,
                'pending_review' => 20,
                'reviewed' => 10,
            ],
            default => [
                'active' => 100,
            ],
        };
    }

    /**
     * Create response workflow log
     */
    private function createResponseWorkflowLog(AssessmentResponse $response, AssessmentResponseStatus $status): void
    {
        if ($status === AssessmentResponseStatus::ACTIVE) {
            return;
        }

        $logs = [
            [
                'from_status' => null,
                'to_status' => AssessmentResponseStatus::ACTIVE->value,
                'transitioned_at' => $response->created_at->subDays(rand(1, 5)),
                'transitioned_by' => $this->getRandomUserId($response->assessment->organization_id),
                'notes' => 'Response initiated',
            ]
        ];

        if ($status === AssessmentResponseStatus::PENDING_REVIEW) {
            $logs[] = [
                'from_status' => AssessmentResponseStatus::ACTIVE->value,
                'to_status' => AssessmentResponseStatus::PENDING_REVIEW->value,
                'transitioned_at' => $response->created_at,
                'transitioned_by' => $this->getRandomUserId($response->assessment->organization_id),
                'notes' => 'Submitted for review',
            ];
        } elseif ($status === AssessmentResponseStatus::REVIEWED) {
            $logs[] = [
                'from_status' => AssessmentResponseStatus::ACTIVE->value,
                'to_status' => AssessmentResponseStatus::PENDING_REVIEW->value,
                'transitioned_at' => (clone $response->created_at)->subDays(rand(1, 3)),
                'transitioned_by' => $this->getRandomUserId($response->assessment->organization_id),
                'notes' => 'Submitted for review',
            ];
            $logs[] = [
                'from_status' => AssessmentResponseStatus::PENDING_REVIEW->value,
                'to_status' => AssessmentResponseStatus::REVIEWED->value,
                'transitioned_at' => $response->created_at,
                'transitioned_by' => $this->getRandomUserId($response->assessment->organization_id),
                'notes' => 'Reviewed and approved',
            ];
        }

        DB::table('assessment_workflow_logs')->insert(array_map(function ($log) use ($response) {
            return [
                'id' => \Illuminate\Support\Str::uuid7()->toString(),
                'loggable_type' => AssessmentResponse::class,
                'loggable_id' => $response->id,
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
     * Get random user ID from organization
     */
    private function getRandomUserId(string $organizationId): ?string
    {
        return \App\Domain\User\Models\User::where('organization_id', $organizationId)
            ->inRandomOrder()
            ->first()?->id;
    }

    /**
     * Get response status enum from value
     */
    private function determineResponseStatusFromValue(string $value): ?AssessmentResponseStatus
    {
        try {
            return AssessmentResponseStatus::from($value);
        } catch (\ValueError $e) {
            return null;
        }
    }
}
