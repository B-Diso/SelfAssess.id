<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;

class CancelAssessment
{
    /**
     * Cancel assessment.
     * Can only be performed by Super Admin.
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

        // Validate user is Super Admin
        if (!$user?->isSuperAdmin()) {
            throw ValidationException::withMessages([
                'status' => ['Only Super Admin can cancel assessments.']
            ]);
        }

        $assessment->status = AssessmentStatus::CANCELLED->value;
        $assessment->save();

        // Sync response statuses
        $assessment->responses()->update(['status' => AssessmentStatus::CANCELLED->value]);

        return $assessment;
    }
}
