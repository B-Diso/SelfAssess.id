<?php

declare(strict_types=1);

namespace App\Domain\Standard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandardRequirementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'standardSectionId' => $this->standard_section_id,
            'displayCode' => $this->display_code,
            'title' => $this->title,
            'description' => $this->description,
            'evidenceHint' => $this->evidence_hint,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
