<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;
use App\Exceptions\Domain\InvariantViolationException;

class FinalizeAssessment
{
    /**
     * Finalize assessment (pending_finish → finished).
     * Can only be performed by users with review-assessments permission.
     *
     * @throws ValidationException
     */
    public function handle(Assessment $assessment, ?User $user = null): Assessment
    {
        $user = $user ?? auth()->user();

        // Validate current status
        if ((string) $assessment->status !== AssessmentStatus::PENDING_FINISH->value) {
            throw ValidationException::withMessages([
                'status' => ['Only assessments pending finish can be finalized.']
            ]);
        }

        // Validate user has permission
        if (!$user?->can('review-assessments')) {
            throw new InvariantViolationException('You do not have permission to finalize assessments.');
        }

        $assessment->status = AssessmentStatus::FINISHED->value;
        $assessment->save();

        // When assessment is finished, requirements stay at 'reviewed'
        // No need to sync response statuses

        return $assessment;
    }
}
