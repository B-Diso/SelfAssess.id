<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Controllers;

use App\Domain\Assessment\Actions\Assessments\UpdateAssessmentResponse;
use App\Domain\Assessment\Actions\Responses\TransitionAssessmentResponseStatus;
use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Assessment\Requests\Responses\ResponseWorkflowRequest;
use App\Domain\Assessment\Requests\Responses\UpdateAssessmentResponseRequest;
use App\Domain\Assessment\Resources\AssessmentResponseResource;
use App\Domain\Assessment\Resources\ResponseWorkflowResponse;
use App\Http\Controllers\Controller;

class AssessmentResponseController extends Controller
{
    public function __construct(
        protected UpdateAssessmentResponse $updateAssessmentResponse,
        protected TransitionAssessmentResponseStatus $transitionResponseStatus
    ) {}

    /**
     * Update the assessment response data.
     */
    public function update(UpdateAssessmentResponseRequest $request, AssessmentResponse $assessmentResponse): AssessmentResponseResource
    {
        $this->authorize('update', $assessmentResponse);

        $updatedResponse = $this->updateAssessmentResponse->handle(
            $assessmentResponse,
            $request->validated()
        );

        return new AssessmentResponseResource($updatedResponse);
    }

    /**
     * Process assessment response workflow transition.
     */
    public function workflow(ResponseWorkflowRequest $request, AssessmentResponse $assessmentResponse): ResponseWorkflowResponse
    {
        $newStatus = $request->validated('status');
        $currentStatus = $assessmentResponse->status->value;

        // Authorize specific transitions based on from/to status
        if ($currentStatus === 'reviewed' && $newStatus === 'active') {
            // reviewed → active: Organization users can cancel reviewed responses (for mistake correction)
            $this->authorize('cancelReviewed', $assessmentResponse);
        } elseif ($currentStatus === 'pending_review' && $newStatus === 'active') {
            // pending_review → active: Organization users can reject/return before review
            $this->authorize('rejectReview', $assessmentResponse);
        } else {
            // Other transitions: follow existing permissions
            $this->authorize('transition', $assessmentResponse);
        }

        $updatedResponse = $this->transitionResponseStatus->handle(
            $assessmentResponse,
            $newStatus,
            $request->validated('note')
        );

        return new ResponseWorkflowResponse($updatedResponse);
    }
}
