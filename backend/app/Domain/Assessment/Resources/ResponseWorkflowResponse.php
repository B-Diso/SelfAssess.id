<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseWorkflowResponse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the latest workflow log for this response
        $latestLog = $this->resource->workflowLogs()
            ->latest('created_at')
            ->with('user')
            ->first();

        return [
            'response' => new AssessmentResponseResource($this->resource),
            'transition' => $latestLog ? new AssessmentWorkflowLogResource($latestLog) : null,
        ];
    }
}
