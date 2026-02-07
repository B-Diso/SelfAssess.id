<?php

declare(strict_types=1);

namespace App\Domain\Standard\Resources;

use App\Domain\Standard\Models\StandardSection;
use App\Domain\Standard\Models\StandardRequirement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandardTreeNodeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $resource = $this->resource;

        if ($resource instanceof StandardSection) {
            return [
                'id' => (string) $resource->id,
                'parentId' => $resource->parent_id ? (string) $resource->parent_id : null,
                'type' => 'section',
                'sectionType' => (string) $resource->type,
                'code' => (string) $resource->code,
                'title' => (string) $resource->title,
                'description' => (string) $resource->description,
                'level' => (int) $resource->level,
                'updatedAt' => $resource->updated_at?->toIso8601String(),
            ];
        }

        if ($resource instanceof StandardRequirement) {
            return [
                'id' => (string) $resource->id,
                'parentId' => (string) $resource->standard_section_id,
                'type' => 'requirement',
                'code' => (string) $resource->display_code,
                'title' => (string) $resource->title,
                'description' => (string) $resource->description,
                'evidenceHint' => (string) $resource->evidence_hint,
                'updatedAt' => $resource->updated_at?->toIso8601String(),
            ];
        }

        // Deep mapping if it's just an array with expected keys
        if (is_array($resource)) {
            return $resource;
        }

        return parent::toArray($request);
    }
}
