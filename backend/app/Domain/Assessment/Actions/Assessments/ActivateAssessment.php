<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use App\Exceptions\Domain\InvariantViolationException;
use Illuminate\Validation\ValidationException;

class ActivateAssessment
{
    /**
     * Activate assessment (draft → active).
     * Can only be performed by users with review-assessments permission.
     *
     * @throws ValidationException
     */
    public function handle(Assessment $assessment, ?User $user = null): Assessment
    {
        $user = $user ?? auth()->user();

        // Validate current status
        if ((string) $assessment->status !== AssessmentStatus::DRAFT->value) {
            throw ValidationException::withMessages([
                'status' => ['Only draft assessments can be activated.']
            ]);
        }

        // Validate user has permission
        if (!$user?->can('review-assessments')) {
            throw new InvariantViolationException('You do not have permission to activate assessments.');
        }

        $assessment->status = AssessmentStatus::ACTIVE->value;
        $assessment->save();

        // Sync response statuses
        $assessment->responses()->update(['status' => AssessmentStatus::ACTIVE->value]);

        return $assessment;
    }
}
