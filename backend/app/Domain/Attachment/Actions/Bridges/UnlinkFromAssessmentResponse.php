<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Actions\Bridges;

use App\Domain\Assessment\Models\AssessmentResponse;

class UnlinkFromAssessmentResponse
{
    /**
     * Unlink an attachment from an assessment response.
     */
    public function execute(AssessmentResponse $response, string $attachmentId): void
    {
        $response->attachments()->detach($attachmentId);
    }
}
