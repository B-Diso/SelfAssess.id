<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentWorkflowResponse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the latest workflow log for this assessment
        $latestLog = $this->resource->workflowLogs()
            ->latest('created_at')
            ->with('user')
            ->first();

        return [
            'assessment' => new AssessmentResource($this->resource),
            'transition' => $latestLog ? new AssessmentWorkflowLogResource($latestLog) : null,
        ];
    }
}
