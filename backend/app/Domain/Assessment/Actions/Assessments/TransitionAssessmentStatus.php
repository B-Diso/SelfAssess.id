<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Assessments;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Enums\AssessmentResponseStatus;
use Illuminate\Validation\ValidationException;

/**
 * Transition Assessment Status
 * 
 * Assessment Workflow:
 * draft → active → pending_review → reviewed → pending_finish → finished
 * 
 * Revert paths (if mistake found):
 * - pending_review → active
 * - reviewed → active  
 * - pending_finish → active
 * - rejected → active
 * 
 * Alternative: cancelled (Super Admin only), rejected
 */
class TransitionAssessmentStatus
{
    /**
     * Valid status transitions map
     */
    private const VALID_TRANSITIONS = [
        'draft'          => ['active'],
        'active'         => ['pending_review'],
        'pending_review' => ['reviewed', 'rejected', 'active'],  // Can revert to active if mistake
        'reviewed'       => ['pending_finish', 'rejected', 'active'],  // Can revert to active if mistake
        'pending_finish' => ['finished', 'rejected', 'reviewed', 'active'],  // Can revert to active if mistake
        'rejected'       => ['active'],
        'cancelled'      => ['active'],
        'finished'       => ['cancelled'],  // Super Admin can cancel finished
    ];

    /**
     * Handle the assessment status transition
     *
     * @throws ValidationException
     */
    public function handle(
        Assessment $assessment, 
        string $newStatus, 
        ?string $note = null
    ): Assessment {
        
        // Get current status as string (model casts to enum)
        $fromStatus = $assessment->status->value;

        // Validate transition
        if (!isset(self::VALID_TRANSITIONS[$fromStatus])) {
            throw ValidationException::withMessages([
                'status' => "Invalid current status: '{$fromStatus}'"
            ]);
        }

        if (!in_array($newStatus, self::VALID_TRANSITIONS[$fromStatus])) {
            $allowed = implode(', ', self::VALID_TRANSITIONS[$fromStatus]);
            throw ValidationException::withMessages([
                'status' => "Cannot transition from '{$fromStatus}' to '{$newStatus}'. Allowed: {$allowed}"
            ]);
        }

        // Validate business rules
        $this->validateBusinessRules($assessment, $newStatus);

        // Update status
        $assessment->status = $newStatus;
        $assessment->save();

        // Sync response statuses (hanya untuk status yang relevan untuk response)
        $this->syncResponseStatuses($assessment, $newStatus);

        // Log the transition
        $assessment->workflowLogs()->create([
            'from_status' => $fromStatus,
            'to_status' => $newStatus,
            'note' => $note,
            'user_id' => auth()->id(),
        ]);

        return $assessment;
    }

    /**
     * Validate business rules before transitioning
     */
    private function validateBusinessRules(Assessment $assessment, string $newStatus): void
    {
        // PENDING_REVIEW: Check all responses are reviewed
        if ($newStatus === AssessmentStatus::PENDING_REVIEW->value) {
            $unreviewedCount = $assessment->responses()
                ->where('status', '!=', AssessmentResponseStatus::REVIEWED)
                ->count();

            if ($unreviewedCount > 0) {
                throw ValidationException::withMessages([
                    'status' => "Cannot submit assessment. {$unreviewedCount} requirement(s) are not yet reviewed."
                ]);
            }
        }
    }

    /**
     * Sync response statuses with assessment status
     * 
     * Response hanya memiliki: active, pending_review, reviewed
     * Hanya sync ke 'active' - status lain adalah assessment-level only
     */
    private function syncResponseStatuses(Assessment $assessment, string $newStatus): void
    {
        // Hanya sync ke 'active' ketika assessment di-reactivate
        if ($newStatus === 'active') {
            $assessment->responses()->update(['status' => 'active']);
        }
        // Note: pending_review, reviewed, pending_finish, finished, rejected, cancelled 
        // adalah assessment-level only, responses tetap pada status mereka
    }
}
