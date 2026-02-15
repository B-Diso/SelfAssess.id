<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;
use App\Exceptions\Domain\InvariantViolationException;

class StartReview
{
    /**
     * Approve assessment review (pending_review → reviewed).
     * Can only be performed by users with review-assessments permission.
     *
     * @throws ValidationException
     */
    public function handle(Assessment $assessment, ?User $user = null): Assessment
    {
        $user = $user ?? auth()->user();

        // Validate current status
        if ((string) $assessment->status !== AssessmentStatus::PENDING_REVIEW->value) {
            throw ValidationException::withMessages([
                'status' => ['Only assessments pending review can be approved.']
            ]);
        }

       // Validate user has permission
       if (!$user?->can('review-assessments')) {
        throw new InvariantViolationException('You do not have permission to approve assessments.');
        }

        $assessment->status = AssessmentStatus::REVIEWED->value;
        $assessment->save();

        // Sync response statuses
        $assessment->responses()->update(['status' => AssessmentStatus::REVIEWED->value]);

        return $assessment;
    }
}
