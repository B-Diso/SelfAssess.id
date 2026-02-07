<?php

declare(strict_types=1);

namespace App\Domain\Report\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResponseHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'fromStatus' => $this->from_status,
            'toStatus' => $this->to_status,
            'note' => $this->note,
            'createdAt' => $this->created_at?->toIso8601String(),
            'user' => $this->whenLoaded('user', fn () => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ]),
        ];
    }
}
