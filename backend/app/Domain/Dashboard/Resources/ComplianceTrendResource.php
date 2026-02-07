<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array $resource
 */
class ComplianceTrendResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'labels' => $this->resource['labels'] ?? [],
            'datasets' => array_map(function ($dataset) {
                return [
                    'label' => $dataset['label'] ?? 'Compliance Rate',
                    'data' => $dataset['data'] ?? [],
                ];
            }, $this->resource['datasets'] ?? []),
        ];
    }
}
