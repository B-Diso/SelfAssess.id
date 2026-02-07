<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;

class RequestFinish
{
    /**
     * Request assessment finish (reviewed → pending_finish).
     * Can only be performed by Org Admin.
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

        // Validate user is Org Admin
        if (!$user?->isOrgAdmin()) {
            throw ValidationException::withMessages([
                'status' => ['Only Organization Admin can request assessment finish.']
            ]);
        }

        $assessment->status = AssessmentStatus::PENDING_FINISH->value;
        $assessment->save();

        // When assessment is pending_finish, requirements stay at 'reviewed'
        // No need to sync response statuses

        return $assessment;
    }
}
