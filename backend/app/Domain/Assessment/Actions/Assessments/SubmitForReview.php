<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;
use App\Exceptions\Domain\InvariantViolationException;

class SubmitForReview
{
    /**
     * Submit assessment for review (active → pending_review).
     * Can be performed by any user with access to the assessment.
     *
     * @throws ValidationException
     */
    public function handle(Assessment $assessment, ?User $user = null): Assessment
    {
        $user = $user ?? auth()->user();

        // Validate current status
        if ((string) $assessment->status !== AssessmentStatus::ACTIVE->value) {
            throw ValidationException::withMessages([
                'status' => ['Only active assessments can be submitted for review.']
            ]);
        }

         // Validate user has access to the assessment's organization
         if (!$user?->canAccessOrganization($assessment->organization_id)) {
            throw new InvariantViolationException('You do not have access to this assessment.');
        }

        $assessment->status = AssessmentStatus::PENDING_REVIEW->value;
        $assessment->save();

        // Sync response statuses
        $assessment->responses()->update(['status' => AssessmentStatus::PENDING_REVIEW->value]);

        return $assessment;
    }
}
