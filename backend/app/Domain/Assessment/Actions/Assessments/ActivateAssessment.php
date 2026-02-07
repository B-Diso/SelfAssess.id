<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;
use Illuminate\Validation\ValidationException;

class ActivateAssessment
{
    /**
     * Activate assessment (draft → active).
     * Can only be performed by Org Admin.
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

        // Validate user is Org Admin
        if (!$user?->isOrgAdmin()) {
            throw ValidationException::withMessages([
                'status' => ['Only Organization Admin can activate assessments.']
            ]);
        }

        $assessment->status = AssessmentStatus::ACTIVE->value;
        $assessment->save();

        // Sync response statuses
        $assessment->responses()->update(['status' => AssessmentStatus::ACTIVE->value]);

        return $assessment;
    }
}
