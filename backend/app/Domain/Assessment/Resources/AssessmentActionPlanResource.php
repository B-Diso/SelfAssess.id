<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentActionPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assessmentId' => $this->assessment_id,
            'assessmentResponseId' => $this->assessment_response_id,
            'title' => $this->title,
            'actionPlan' => $this->action_plan,
            'pic' => $this->pic,
            'dueDate' => $this->due_date?->toDateString(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
