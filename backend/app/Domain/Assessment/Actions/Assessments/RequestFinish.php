<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;
use App\Exceptions\Domain\InvariantViolationException;

class RequestFinish
{
    /**
     * Request assessment finish (reviewed → pending_finish).
     * Can only be performed by users with review-assessments permission.
     * This submits the assessment to Super Admin for finalization.
     *
     * @throws ValidationException
     */
    public function handle(Assessment $assessment, ?User $user = null): Assessment
    {
        $user = $user ?? auth()->user();

        // Validate current status
        if ((string) $assessment->status !== AssessmentStatus::REVIEWED->value) {
            throw ValidationException::withMessages([
                'status' => ['Only reviewed assessments can be submitted for finish.']
            ]);
        }

        // Validate user has permission
        if (!$user?->can('review-assessments')) {
            throw new InvariantViolationException('You do not have permission to request assessment finish.');
        }

        $assessment->status = AssessmentStatus::PENDING_FINISH->value;
        $assessment->save();

        // When assessment is pending_finish, requirements stay at 'reviewed'
        // No need to sync response statuses

        return $assessment;
    }
}
