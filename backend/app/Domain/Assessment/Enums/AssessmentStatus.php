<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Enums;

/**
 * Assessment (Parent) Workflow Statuses
 * 
 * Flow: draft → active → pending_review → reviewed → pending_finish → finished
 * Alternative paths: rejected, cancelled
 * 
 * Note: This is DIFFERENT from AssessmentResponse status flow!
 * For individual requirement responses, use AssessmentResponseStatus enum.
 */
enum AssessmentStatus: string
{
    case DRAFT = 'draft';                    // Initial state
    case ACTIVE = 'active';                  // Organization user can input
    case PENDING_REVIEW = 'pending_review';  // Submitted for org admin review
    case REVIEWED = 'reviewed';              // Reviewed by org admin
    case PENDING_FINISH = 'pending_finish';  // Org requested finish, pending super admin
    case FINISHED = 'finished';              // Final state (super admin only)
    case REJECTED = 'rejected';              // Returned for changes
    case CANCELLED = 'cancelled';            // Cancelled, can reactivate

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
