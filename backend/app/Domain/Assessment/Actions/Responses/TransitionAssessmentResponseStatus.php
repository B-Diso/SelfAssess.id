<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Actions\Responses;

use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Assessment\Enums\AssessmentResponseStatus;
use Illuminate\Validation\ValidationException;

/**
 * Transition Assessment Response Status
 * 
 * Response Workflow: active → pending_review → reviewed
 * 
 * Note: Responses do NOT have cancelled/rejected/finished states.
 * These are assessment-level only. Responses can only go back to active.
 * 
 * Valid Transitions:
 * - active → pending_review (Org User submit)
 * - pending_review → reviewed (Org Admin approve)
 * - pending_review → active (Org Admin reject/return)
 * - reviewed → active (Org Admin revert if mistake)
 */
class TransitionAssessmentResponseStatus
{
    /**
     * Valid status transitions map
     */
    private const VALID_TRANSITIONS = [
        'active'         => ['pending_review'],
        'pending_review' => ['reviewed', 'active'],
        'reviewed'       => ['active'],
    ];

    /**
     * Handle the response status transition
     *
     * @throws ValidationException
     */
    public function handle(
        AssessmentResponse $response, 
        string $newStatus, 
        ?string $note = null
    ): AssessmentResponse {
        
        // Get current status as string (model casts to enum)
        $fromStatus = $response->status->value;

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

        // Update status
        $response->status = $newStatus;
        $response->save();

        // Log the transition
        $response->workflowLogs()->create([
            'from_status' => $fromStatus,
            'to_status' => $newStatus,
            'note' => $note,
            'user_id' => auth()->id(),
        ]);

        return $response;
    }
}
