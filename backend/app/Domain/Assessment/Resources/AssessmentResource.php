<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResource extends JsonResource
{
    /**
     * Calculate progress based on requirement statuses.
     * Each requirement contributes:
     * - active = 0%
     * - pending_review = 50%
     * - reviewed = 100%
     * 
     * Uses pre-calculated counts from withCount to avoid N+1
     */
    private function calculateRequirementProgress(): int
    {
        $total = $this->responses_count ?? 0;
        
        if ($total === 0) {
            return 0;
        }
        
        $completed = $this->completed_responses_count ?? 0;
        $pendingReview = $this->pending_review_responses_count ?? 0;
        
        // Calculate weighted progress:
        // - reviewed (completed) = 100%
        // - pending_review = 50%
        // - active = 0%
        $totalScore = ($completed * 100) + ($pendingReview * 50);
        
        return (int) round($totalScore / $total);
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organizationId' => $this->organization_id,
            'standardId' => $this->standard_id,
            'name' => $this->name,
            'periodValue' => $this->period_value,
            'startDate' => $this->start_date?->format('Y-m-d'),
            'endDate' => $this->end_date?->format('Y-m-d'),
            'status' => $this->status,
            'organizationName' => $this->whenLoaded('organization', fn () => $this->organization?->name),
            'standardName' => $this->whenLoaded('standard', fn () => $this->standard?->name),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            'total' => $this->responses_count ?? 0,
            'completed' => $this->completed_responses_count ?? 0,
            'pendingReview' => $this->pending_review_responses_count ?? 0,
            'percentage' => $this->calculateRequirementProgress(),
            'responses' => AssessmentResponseResource::collection($this->whenLoaded('responses')),
        ];
    }
}
