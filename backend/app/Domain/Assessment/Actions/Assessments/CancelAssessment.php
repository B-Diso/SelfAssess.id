<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;
use App\Exceptions\Domain\InvariantViolationException;

class CancelAssessment
{
    /**
     * Cancel assessment.
     * Can only be performed by users with review-assessments permission.
     * Draft assessments cannot be cancelled (use delete instead).
     * Finished assessments can be cancelled to revert them.
     *
     * @throws ValidationException
     */
    public function handle(Assessment $assessment, ?User $user = null): Assessment
    {
        $user = $user ?? auth()->user();

        // Validate current status - cannot cancel draft
        if ((string) $assessment->status === AssessmentStatus::DRAFT->value) {
            throw ValidationException::withMessages([
                'status' => ['Draft assessments cannot be cancelled. Delete them instead.']
            ]);
        }

       // Validate user has permission
       if (!$user?->can('review-assessments')) {
        throw new InvariantViolationException('You do not have permission to cancel assessments.');
       }

        $assessment->status = AssessmentStatus::CANCELLED->value;
        $assessment->save();

        // Sync response statuses
        $assessment->responses()->update(['status' => AssessmentStatus::CANCELLED->value]);

        return $assessment;
    }
}
