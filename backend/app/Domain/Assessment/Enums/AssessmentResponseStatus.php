<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Enums;

/**
 * Assessment Response (Requirement) Workflow Statuses
 *
 * Flow: active → pending_review → reviewed
 *
 * Valid Transitions:
 * - active → pending_review (User finished filling)
 * - pending_review → reviewed (Reviewer approves)
 * - pending_review → active (Reviewer returns/rejects)
 * - reviewed → active (Admin found error, needs correction)
 *
 * Note: Different from Assessment status flow!
 */
enum AssessmentResponseStatus: string
{
    case ACTIVE = 'active';               // Initial state, user can fill/edit
    case PENDING_REVIEW = 'pending_review'; // User finished, waiting for reviewer
    case REVIEWED = 'reviewed';           // Reviewer has approved

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
