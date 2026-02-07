<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array $resource
 */
class ActionPlanSummaryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'overdue' => array_map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'title' => $item['title'] ?? null,
                    'assessment' => isset($item['assessment']) ? [
                        'id' => $item['assessment']['id'] ?? null,
                        'name' => $item['assessment']['name'] ?? null,
                    ] : null,
                    'organization' => $item['organization'] ?? null,
                    'dueDate' => $item['dueDate'] ?? null,
                    'daysOverdue' => $item['daysOverdue'] ?? 0,
                    'pic' => $item['pic'] ?? null,
                ];
            }, $this->resource['overdue'] ?? []),
            'upcoming' => array_map(function ($item) {
                return [
                    'id' => $item['id'] ?? null,
                    'title' => $item['title'] ?? null,
                    'assessment' => isset($item['assessment']) ? [
                        'id' => $item['assessment']['id'] ?? null,
                        'name' => $item['assessment']['name'] ?? null,
                    ] : null,
                    'organization' => $item['organization'] ?? null,
                    'dueDate' => $item['dueDate'] ?? null,
                    'daysRemaining' => $item['daysRemaining'] ?? 0,
                    'pic' => $item['pic'] ?? null,
                ];
            }, $this->resource['upcoming'] ?? []),
        ];
    }
}
