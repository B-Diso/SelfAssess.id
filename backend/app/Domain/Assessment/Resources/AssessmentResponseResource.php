<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentResponseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assessmentId' => $this->assessment_id,
            'standardRequirementId' => $this->standard_requirement_id,
            'status' => $this->status,
            'comments' => $this->comments,
            'complianceStatus' => $this->compliance_status,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
            // Flattened requirement fields (when loaded)
            'requirementId' => $this->whenLoaded('requirement', fn () => $this->requirement?->id),
            'requirementDisplayCode' => $this->whenLoaded('requirement', fn () => $this->requirement?->display_code),
            'requirementDescription' => $this->whenLoaded('requirement', fn () => $this->requirement?->description),
            'requirementTitle' => $this->whenLoaded('requirement', fn () => $this->requirement?->title),
            'requirementEvidenceHint' => $this->whenLoaded('requirement', fn () => $this->requirement?->evidence_hint),
        ];
    }
}
