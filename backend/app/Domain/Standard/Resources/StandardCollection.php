<?php

namespace App\Domain\Standard\Resources;

use App\Traits\CleansPaginationResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class StandardCollection extends ResourceCollection
{
    use CleansPaginationResponse;

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
