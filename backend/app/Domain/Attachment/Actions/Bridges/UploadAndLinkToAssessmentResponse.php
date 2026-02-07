<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Actions\Bridges;

use App\Domain\Attachment\Actions\Attachments\CreateAttachment;
use App\Domain\Assessment\Models\AssessmentResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class UploadAndLinkToAssessmentResponse
{
    public function __construct(
        protected CreateAttachment $createAttachment
    ) {}

    /**
     * Upload a file to the warehouse and immediately link it to an assessment response.
     */
    public function execute(AssessmentResponse $response, UploadedFile $file, string $userId, array $data = []): void
    {
        \Log::info('UploadAndLinkToAssessmentResponse: Starting', [
            'response_id' => $response->id,
            'assessment_id' => $response->assessment_id,
            'file_name' => $file->getClientOriginalName(),
        ]);

        // 1. Create the attachment in the warehouse (Document Center)
        $attachment = $this->createAttachment->handle([
            'organization_id' => $response->assessment->organization_id,
            'category' => 'evidence',
            'description' => $data['description'] ?? null,
        ], $file, $userId);

        \Log::info('UploadAndLinkToAssessmentResponse: Attachment created', [
            'attachment_id' => $attachment->id,
        ]);

        // 2. Link it to the assessment response
        $response->attachments()->attach($attachment->id, [
            'id' => (string) Str::uuid(),
            'assessment_id' => $response->assessment_id,
        ]);

        \Log::info('UploadAndLinkToAssessmentResponse: Linked to response', [
            'response_id' => $response->id,
            'attachment_id' => $attachment->id,
        ]);
    }
}
