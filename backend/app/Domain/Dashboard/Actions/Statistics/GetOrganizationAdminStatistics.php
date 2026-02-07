<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions\Statistics;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Models\AssessmentActionPlan;
use App\Domain\User\Models\User;
use Carbon\Carbon;

class GetOrganizationAdminStatistics
{
    /**
     * Get statistics for Organization Admin.
     */
    public function handle(string $organizationId): array
    {
        // Count organization users
        $totalUsers = User::where('organization_id', $organizationId)
            ->count();

        // Count active assessments in organization
        $activeAssessments = Assessment::where('organization_id', $organizationId)
            ->whereIn('status', ['active', 'pending_review', 'reviewed'])
            ->count();

        // Calculate average completion rate
        $completionRate = $this->calculateOrganizationCompletionRate($organizationId);

        // Count pending action plans (overdue or due within 7 days)
        $pendingActionPlans = AssessmentActionPlan::whereHas('assessment', function ($query) use ($organizationId) {
            $query->where('organization_id', $organizationId);
        })
            ->where('due_date', '<=', Carbon::now()->addDays(7))
            ->count();

        return [
            'totalUsers' => $totalUsers,
            'activeAssessments' => $activeAssessments,
            'completionRate' => $completionRate,
            'pendingActionPlans' => $pendingActionPlans,
        ];
    }

    /**
     * Calculate completion rate for an organization.
     */
    private function calculateOrganizationCompletionRate(string $organizationId): float
    {
        $assessments = Assessment::where('organization_id', $organizationId)
            ->whereIn('status', ['active', 'pending_review', 'reviewed', 'finished'])
            ->get();

        if ($assessments->isEmpty()) {
            return 0.0;
        }

        $totalProgress = 0;
        foreach ($assessments as $assessment) {
            $totalProgress += $this->calculateAssessmentProgress($assessment);
        }

        return round($totalProgress / $assessments->count(), 2);
    }

    /**
     * Calculate progress based on requirement statuses.
     * Each requirement contributes:
     * - draft/active/rejected/cancelled = 0%
     * - submitted = 50%
     * - reviewed/finished = 100%
     */
    private function calculateAssessmentProgress(Assessment $assessment): int
    {
        $responses = $assessment->responses;

        if ($responses->isEmpty()) {
            return 0;
        }

        $totalScore = 0;
        foreach ($responses as $response) {
            $totalScore += match ($response->status->value) {
                'reviewed' => 100,
                'pending_review' => 50,
                default => 0, // draft, active, rejected, cancelled
            };
        }

        return (int) round($totalScore / $responses->count());
    }
}
