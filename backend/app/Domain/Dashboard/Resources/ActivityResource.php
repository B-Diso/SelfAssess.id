<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array $resource
 */
class ActivityResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'] ?? null,
            'type' => $this->resource['type'] ?? null,
            'description' => $this->resource['description'] ?? null,
            'user' => $this->resource['user'] ?? null,
            'organization' => $this->resource['organization'] ?? null,
            'status' => $this->resource['status'] ?? null,
            'createdAt' => $this->resource['createdAt'] ?? null,
        ];
    }
}
