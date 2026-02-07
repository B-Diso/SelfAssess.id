<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions\Assessments;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Models\AssessmentResponse;
use Illuminate\Support\Facades\DB;

class GetOrganizationAssessmentSummary
{
    /**
     * Get organization assessment summary by status.
     */
    public function handle(string $organizationId): array
    {
        // Count by status for organization
        $byStatus = Assessment::where('organization_id', $organizationId)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // Ensure all statuses are represented
        $allStatuses = ['draft', 'active', 'submitted', 'reviewed', 'finished', 'rejected', 'cancelled'];
        foreach ($allStatuses as $status) {
            if (!isset($byStatus[$status])) {
                $byStatus[$status] = 0;
            }
        }

        // Get recent assessments for organization
        $recent = Assessment::where('organization_id', $organizationId)
            ->with(['organization', 'responses'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($assessment) {
                return [
                    'id' => $assessment->id,
                    'name' => $assessment->name,
                    'organization' => $assessment->organization?->name,
                    'status' => $assessment->status,
                    'progress' => $this->calculateAssessmentProgress($assessment),
                    'createdAt' => $assessment->created_at->toISOString(),
                ];
            })
            ->toArray();

        return [
            'byStatus' => $byStatus,
            'recent' => $recent,
        ];
    }

    /**
     * Calculate progress based on requirement statuses.
     * Each requirement contributes:
     * - active = 0%
     * - pending_review = 50%
     * - reviewed = 100%
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
                default => 0, // active
            };
        }
        
        return (int) round($totalScore / $responses->count());
    }
}
