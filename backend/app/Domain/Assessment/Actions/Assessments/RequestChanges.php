<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;

class RequestChanges
{
    /**
     * Request changes/revert to active state.
     * Can be used from pending_review, reviewed, or pending_finish states.
     * Can only be performed by Org Admin.
     *
     * @throws ValidationException
     */
    public function handle(Assessment $assessment, ?User $user = null): Assessment
    {
        $user = $user ?? auth()->user();

        // Validate current status - can revert from these states
        $validStates = [
            AssessmentStatus::PENDING_REVIEW->value,
            AssessmentStatus::REVIEWED->value,
            AssessmentStatus::PENDING_FINISH->value,
        ];

        if (!in_array((string) $assessment->status, $validStates)) {
            throw ValidationException::withMessages([
                'status' => ['Can only request changes from pending_review, reviewed, or pending_finish states.']
            ]);
        }

        // Validate user is Org Admin
        if (!$user?->isOrgAdmin()) {
            throw ValidationException::withMessages([
                'status' => ['Only Organization Admin can request changes.']
            ]);
        }

        $assessment->status = AssessmentStatus::ACTIVE->value;
        $assessment->save();

        // Sync response statuses
        $assessment->responses()->update(['status' => AssessmentStatus::ACTIVE->value]);

        return $assessment;
    }
}
