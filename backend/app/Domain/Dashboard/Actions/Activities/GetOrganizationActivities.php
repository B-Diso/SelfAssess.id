<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions\Activities;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Assessment\Models\AssessmentWorkflowLog;

class GetOrganizationActivities
{
    /**
     * Get organization activities.
     */
    public function handle(string $organizationId, int $limit = 10): array
    {
        // Get assessments from this organization
        $assessmentIds = Assessment::where('organization_id', $organizationId)
            ->pluck('id')
            ->toArray();

        // Get workflow logs for these assessments
        $activities = AssessmentWorkflowLog::with(['user', 'loggable'])
            ->where(function ($query) use ($assessmentIds) {
                $query->whereIn('loggable_id', $assessmentIds)
                    ->where('loggable_type', Assessment::class);
            })
            ->orWhereHasMorph('loggable', [AssessmentResponse::class], function ($query) use ($assessmentIds) {
                $query->whereIn('assessment_id', $assessmentIds);
            })
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'type' => 'workflow',
                    'description' => $this->formatWorkflowLogDescription($log),
                    'user' => $log->user ? [
                        'id' => $log->user->id,
                        'name' => $log->user->name,
                    ] : null,
                    'organization' => null,
                    'createdAt' => $log->created_at->toISOString(),
                ];
            })
            ->toArray();

        // If no workflow logs, get recent assessments from org
        if (empty($activities)) {
            $activities = Assessment::where('organization_id', $organizationId)
                ->with(['organization'])
                ->latest()
                ->limit($limit)
                ->get()
                ->map(function ($assessment) {
                    return [
                        'id' => $assessment->id,
                        'type' => 'assessment_created',
                        'description' => "Assessment '{$assessment->name}' created",
                        'user' => null,
                        'organization' => $assessment->organization ? [
                            'id' => $assessment->organization->id,
                            'name' => $assessment->organization->name,
                        ] : null,
                        'createdAt' => $assessment->created_at->toISOString(),
                    ];
                })
                ->toArray();
        }

        return $activities;
    }

    /**
     * Format workflow log description.
     */
    private function formatWorkflowLogDescription(AssessmentWorkflowLog $log): string
    {
        $loggableType = class_basename($log->loggable_type);
        $from = $log->from_status ? "from '{$log->from_status}'" : '';
        $to = "to '{$log->to_status}'";

        return "{$loggableType} status changed {$from} {$to}";
    }
}
