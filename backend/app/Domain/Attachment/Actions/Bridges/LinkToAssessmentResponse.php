<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Actions\Bridges;

use App\Domain\Attachment\Models\Attachment;
use App\Domain\Assessment\Models\AssessmentResponse;
use Illuminate\Support\Str;

class LinkToAssessmentResponse
{
    /**
     * Link an attachment from the Document Center as evidence for an assessment response.
     */
    public function execute(AssessmentResponse $response, string $attachmentId): void
    {
        $attachment = Attachment::findOrFail($attachmentId);

        $response->attachments()->syncWithoutDetaching([
            $attachment->id => [
                'id' => (string) Str::uuid(),
                'assessment_id' => $response->assessment_id,
            ]
        ]);
    }
}
