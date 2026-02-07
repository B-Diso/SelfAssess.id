<?php

namespace App\Domain\Standard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandardElementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'standardDomainId' => $this->standard_domain_id,
            'standardDomainName' => $this->domain->code,
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'position' => $this->position,
            'isActive' => $this->is_active,
        ];
    }
}
