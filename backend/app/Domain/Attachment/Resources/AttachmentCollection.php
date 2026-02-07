<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Resources;

use App\Traits\CleansPaginationResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AttachmentCollection extends ResourceCollection
{
    use CleansPaginationResponse;

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = AttachmentResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
