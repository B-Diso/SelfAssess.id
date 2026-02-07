<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'organizationId' => $this->organization_id,
            'name' => $this->name,
            'mimeType' => $this->mime_type,
            'size' => $this->size,
            'category' => $this->category,
            'description' => $this->description,
            'createdById' => $this->created_by_id,
            'createdByName' => $this->creator?->name,
            'updatedById' => $this->updated_by_id,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'downloadUrl' => route('attachments.download', ['attachment' => $this->id]),
        ];
    }
}
