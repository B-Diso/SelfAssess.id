<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions\Statistics;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Models\AssessmentActionPlan;
use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\User\Models\User;

class GetUserStatistics
{
    /**
     * Get statistics for Regular User.
     */
    public function handle(User $user): array
    {
        // Count user's assessments (from their organization)
        $myAssessments = Assessment::where('organization_id', $user->organization_id)
            ->whereIn('status', ['active', 'pending_review', 'reviewed', 'finished'])
            ->count();

        // Calculate completion progress
        $completionProgress = $this->calculateUserCompletionProgress($user);

        // Count open requirements (responses with active status)
        $openRequirements = $this->countOpenRequirements($user);

        // Count organization's action plans
        $orgActionPlans = AssessmentActionPlan::whereHas('assessment', function ($query) use ($user) {
            $query->where('organization_id', $user->organization_id);
        })->count();

        return [
            'myAssessments' => $myAssessments,
            'completionProgress' => $completionProgress,
            'openRequirements' => $openRequirements,
            'orgActionPlans' => $orgActionPlans,
        ];
    }

    /**
     * Calculate user's completion progress.
     */
    private function calculateUserCompletionProgress(User $user): float
    {
        $assessments = Assessment::where('organization_id', $user->organization_id)
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

    /**
     * Count open requirements (responses with active status).
     */
    private function countOpenRequirements(User $user): int
    {
        // Get assessment IDs in user's organization
        $assessmentIds = Assessment::where('organization_id', $user->organization_id)
            ->whereIn('status', ['active', 'pending_review', 'reviewed'])
            ->pluck('id')
            ->toArray();

        // Count responses with active status (still open, not yet reviewed)
        return AssessmentResponse::whereIn('assessment_id', $assessmentIds)
            ->where('status', 'active')
            ->whereNotIn('compliance_status', ['not_applicable'])
            ->count();
    }
}
