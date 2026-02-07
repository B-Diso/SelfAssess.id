<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array $resource
 */
class AssessmentSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $byStatus = $this->resource['byStatus'] ?? [];

        return [
            'byStatus' => [
                'draft' => $byStatus['draft'] ?? 0,
                'active' => $byStatus['active'] ?? 0,
                'submitted' => $byStatus['submitted'] ?? 0,
                'reviewed' => $byStatus['reviewed'] ?? 0,
                'finished' => $byStatus['finished'] ?? 0,
                'rejected' => $byStatus['rejected'] ?? 0,
                'cancelled' => $byStatus['cancelled'] ?? 0,
            ],
            'recent' => array_map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'name' => $item['name'] ?? null,
                    'organizationName' => $item['organization'] ?? null,
                    'status' => $item['status'] ?? null,
                    'progress' => $item['progress'] ?? 0,
                    'createdAt' => $item['createdAt'] ?? null,
                ];
            }, $this->resource['recent'] ?? []),
        ];
    }
}
