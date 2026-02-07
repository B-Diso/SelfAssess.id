<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;

class StartReview
{
    /**
     * Approve assessment review (pending_review → reviewed).
     * Can only be performed by Org Admin.
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

        // Validate user is Org Admin
        if (!$user?->isOrgAdmin()) {
            throw ValidationException::withMessages([
                'status' => ['Only Organization Admin can approve assessments.']
            ]);
        }

        $assessment->status = AssessmentStatus::REVIEWED->value;
        $assessment->save();

        // Sync response statuses
        $assessment->responses()->update(['status' => AssessmentStatus::REVIEWED->value]);

        return $assessment;
    }
}
