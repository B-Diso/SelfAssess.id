<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssessmentWorkflowLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fromStatus' => $this->from_status,
            'toStatus' => $this->to_status,
            'note' => $this->note,
            'userId' => $this->user_id,
            'userName' => $this->whenLoaded('user', fn () => $this->user?->name),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
