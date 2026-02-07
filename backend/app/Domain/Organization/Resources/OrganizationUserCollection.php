<?php

namespace App\Domain\Organization\Resources;

use App\Domain\User\Resources\OrganizationUserResource;
use App\Traits\CleansPaginationResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class OrganizationUserCollection extends ResourceCollection
{
    use CleansPaginationResponse;

    public $collects = OrganizationUserResource::class;

    /**
     * Transform the resource collection into an array.
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
