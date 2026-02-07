<?php

declare(strict_types=1);

namespace App\Domain\Report\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandardReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'version' => $this->version,
            'createdAt' => $this->created_at?->toIso8601String(),
            'stats' => $this->stats ?? [
                'totalOrganizations' => 0,
                'startedOrganizations' => 0,
                'notStartedOrganizations' => 0,
            ],
        ];
    }
}
