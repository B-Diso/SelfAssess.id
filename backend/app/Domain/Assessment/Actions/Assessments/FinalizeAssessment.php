<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;

class FinalizeAssessment
{
    /**
     * Finalize assessment (pending_finish → finished).
     * Can only be performed by Super Admin.
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

        // Validate user is Super Admin
        if (!$user?->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'status' => ['Only Super Admin can finalize assessments.']
            ]);
        }

        $assessment->status = AssessmentStatus::FINISHED->value;
        $assessment->save();

        // When assessment is finished, requirements stay at 'reviewed'
        // No need to sync response statuses

        return $assessment;
    }
}
