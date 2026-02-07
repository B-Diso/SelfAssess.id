<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Controllers;

use App\Domain\Attachment\Actions\Bridges\LinkToAssessmentResponse;
use App\Domain\Attachment\Actions\Bridges\UnlinkFromAssessmentResponse;
use App\Domain\Attachment\Actions\Bridges\UploadAndLinkToAssessmentResponse;
use App\Domain\Attachment\Requests\LinkAssessmentAttachmentRequest;
use App\Domain\Attachment\Requests\UnlinkAssessmentAttachmentRequest;
use App\Domain\Attachment\Requests\UploadAssessmentAttachmentRequest;
use App\Domain\Attachment\Resources\AttachmentCollection;
use App\Domain\Attachment\Resources\AttachmentResource;
use App\Domain\Attachment\Resources\MessageResource;
use App\Domain\Assessment\Models\AssessmentResponse;
use App\Http\Controllers\Controller;

class AttachmentBridgeController extends Controller
{
    public function __construct(
        protected LinkToAssessmentResponse $linkToAssessmentAction,
        protected UnlinkFromAssessmentResponse $unlinkFromAssessmentAction,
        protected UploadAndLinkToAssessmentResponse $uploadAndLinkAssessmentAction
    ) {}

    /**
     * Get attachments linked to an Assessment Response.
     */
    public function getAssessmentResponseAttachments(AssessmentResponse $assessment_response): AttachmentCollection
    {
        \Log::info('getAssessmentResponseAttachments', [
            'response_id' => $assessment_response->id,
            'attachments_count' => $assessment_response->attachments()->count(),
        ]);

        $attachments = $assessment_response->attachments()->with(['creator'])->get();

        \Log::info('getAssessmentResponseAttachments loaded', [
            'count' => $attachments->count(),
            'ids' => $attachments->pluck('id')->toArray(),
        ]);

        return new AttachmentCollection($attachments);
    }

    /**
     * Link attachment to Assessment Response (Evidence).
     */
    public function linkToAssessmentResponse(LinkAssessmentAttachmentRequest $request): MessageResource
    {
        $response = AssessmentResponse::findOrFail($request->validated('assessment_response_id'));

        // Explicit authorization check
        $this->authorize('view', $response->assessment);

        \Log::info('linkToAssessmentResponse: Request received', [
            'response_id' => $response->id,
            'attachment_id' => $request->validated('attachment_id'),
        ]);

        $this->linkToAssessmentAction->execute($response, $request->validated('attachment_id'));

        \Log::info('linkToAssessmentResponse: Success');

        return new MessageResource([
            'message' => 'Attachment linked to assessment response successfully'
        ]);
    }

    /**
     * Upload and link to Assessment Response.
     */
    public function uploadToAssessmentResponse(UploadAssessmentAttachmentRequest $request): MessageResource
    {
        \Log::info('uploadToAssessmentResponse: Request received', [
            'assessment_response_id' => $request->validated('assessment_response_id'),
            'has_file' => $request->hasFile('file'),
        ]);

        $response = AssessmentResponse::findOrFail($request->validated('assessment_response_id'));

        // Explicit authorization check
        $this->authorize('view', $response->assessment);

        $this->uploadAndLinkAssessmentAction->execute(
            $response,
            $request->file('file'),
            (string) $request->user()?->id,
            $request->validated()
        );

        \Log::info('uploadToAssessmentResponse: Success');

        return new MessageResource([
            'message' => 'File uploaded and linked to assessment response successfully'
        ]);
    }

    /**
     * Unlink attachment from Assessment Response.
     */
    public function unlinkFromAssessmentResponse(UnlinkAssessmentAttachmentRequest $request, string $assessment_response, string $attachment): MessageResource
    {
        // Manually resolve route parameters to avoid model binding conflicts
        $response = AssessmentResponse::findOrFail($assessment_response);

        // Explicit authorization check
        $this->authorize('view', $response->assessment);

        \Log::info('unlinkFromAssessmentResponse: Request received', [
            'response_id' => $response->id,
            'assessment_id' => $response->assessment_id,
            'attachment_id' => $attachment,
            'user_id' => auth()->id(),
            'assessment_status' => $response->assessment->status,
        ]);

        $this->unlinkFromAssessmentAction->execute($response, $attachment);

        \Log::info('unlinkFromAssessmentResponse: Success');

        return new MessageResource([
            'message' => 'Attachment unlinked from assessment response successfully'
        ]);
    }
}
