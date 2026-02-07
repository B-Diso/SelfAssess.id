<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions\Statistics;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Models\AssessmentWorkflowLog;
use App\Domain\User\Models\User;
use Carbon\Carbon;

class GetReviewerStatistics
{
    /**
     * Get statistics for Reviewer (user with review-assessments permission).
     */
    public function handle(User $user): array
    {
        $organizationId = $user->organization_id;

        // Count pending reviews (submitted assessments in user's org)
        $pendingReviews = Assessment::where('organization_id', $organizationId)
            ->where('status', 'submitted')
            ->count();

        // Count reviewed this month
        $reviewedThisMonth = AssessmentWorkflowLog::where('user_id', $user->id)
            ->where('to_status', 'reviewed')
            ->whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // Calculate average review time (in days)
        $avgReviewTime = $this->calculateAverageReviewTime($user);

        // Count rejected assessments (reviewed by this user or in org)
        $rejectedAssessments = Assessment::where('organization_id', $organizationId)
            ->where('status', 'rejected')
            ->whereMonth('updated_at', Carbon::now()->month)
            ->whereYear('updated_at', Carbon::now()->year)
            ->count();

        return [
            'pendingReviews' => $pendingReviews,
            'reviewedThisMonth' => $reviewedThisMonth,
            'avgReviewTime' => $avgReviewTime,
            'rejectedAssessments' => $rejectedAssessments,
        ];
    }

    /**
     * Calculate average review time in days.
     */
    private function calculateAverageReviewTime(User $user): float
    {
        $logs = AssessmentWorkflowLog::where('user_id', $user->id)
            ->where('to_status', 'reviewed')
            ->whereNotNull('created_at')
            ->get();

        if ($logs->isEmpty()) {
            return 0.0;
        }

        $totalDays = 0;
        $count = 0;

        foreach ($logs as $log) {
            // Find the submitted log for the same assessment/response
            $submittedLog = AssessmentWorkflowLog::where('loggable_id', $log->loggable_id)
                ->where('loggable_type', $log->loggable_type)
                ->where('to_status', 'submitted')
                ->where('created_at', '<', $log->created_at)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($submittedLog) {
                $totalDays += $submittedLog->created_at->diffInDays($log->created_at);
                $count++;
            }
        }

        return $count > 0 ? round($totalDays / $count, 1) : 0.0;
    }
}
