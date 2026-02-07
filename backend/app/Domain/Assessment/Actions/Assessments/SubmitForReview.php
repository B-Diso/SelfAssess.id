<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;

class SubmitForReview
{
    /**
     * Submit assessment for review (active → pending_review).
     * Can be performed by Org User or Org Admin with access to the assessment.
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

        // Validate user has access to this assessment
        if (!$user->isOrgAdmin() && !$assessment->isAccessibleBy($user)) {
            throw ValidationException::withMessages([
                'assessment' => ['You do not have permission to submit this assessment.']
            ]);
        }

        // Validate all responses are reviewed
        $unreviewedCount = $assessment->responses()
            ->where('status', '!=', \App\Domain\Assessment\Enums\AssessmentResponseStatus::REVIEWED->value)
            ->count();

        if ($unreviewedCount > 0) {
            throw ValidationException::withMessages([
                'status' => ["Cannot submit assessment. {$unreviewedCount} requirement(s) are not yet reviewed."]
            ]);
        }

        $assessment->status = AssessmentStatus::PENDING_REVIEW->value;
        $assessment->save();

        // Sync response statuses
        $assessment->responses()->update(['status' => AssessmentStatus::PENDING_REVIEW->value]);

        return $assessment;
    }
}
