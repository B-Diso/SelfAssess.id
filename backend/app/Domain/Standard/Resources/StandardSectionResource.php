<?php

namespace App\Domain\Standard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandardSectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'standardId' => $this->standard_id,
            'parentId' => $this->parent_id,
            'type' => $this->type,
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'level' => $this->level,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'children' => $this->whenLoaded('children', fn() => StandardSectionResource::collection($this->children)),
            'requirements' => $this->whenLoaded('requirements', fn() => StandardRequirementResource::collection($this->requirements)),
        ];
    }
}
