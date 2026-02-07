<?php

namespace App\Domain\Assessment\Resources;

use App\Traits\CleansPaginationResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class AssessmentResponseCollection extends ResourceCollection
{
    use CleansPaginationResponse;

    /**
     * Transform resource collection into an array.
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}