<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Policies;

use App\Domain\Assessment\Enums\AssessmentResponseStatus;
use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\User\Models\User;

class AssessmentResponsePolicy
{
    /**
     * Determine if user can view the assessment response.
     */
    public function view(User $user, AssessmentResponse $response): bool
    {
        $assessment = $response->assessment;

        return $user->can('view-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Determine if assessment response can be updated (input phase).
     */
    public function update(User $user, AssessmentResponse $response): bool
    {
        $assessment = $response->assessment;

        // Check parent assessment status - use AssessmentStatus enum
        if (in_array($assessment->status, [
            AssessmentStatus::FINISHED,
            AssessmentStatus::CANCELLED,
            AssessmentStatus::PENDING_REVIEW,
            AssessmentStatus::REVIEWED,
        ])) {
            return false;
        }

        // Check individual response status - use AssessmentResponseStatus enum
        // Only update if response is in active status
        if (in_array($response->status, [
            AssessmentResponseStatus::PENDING_REVIEW,  // Can't edit pending_review
            AssessmentResponseStatus::REVIEWED,         // Can't edit reviewed responses
        ])) {
            return false;
        }

        return $user->can('view-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Determine if assessment response can be transitioned.
     * This is a general check - specific transitions have their own methods.
     */
    public function transition(User $user, AssessmentResponse $response): bool
    {
        $assessment = $response->assessment;

        // Check parent assessment status - finished assessment can't transition responses
        if ($assessment->status === AssessmentStatus::FINISHED) {
            return $user->can('review-assessments');
        }

        return $user->can('view-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Determine if user can reject/return response (pending_review → active).
     * Context: User returns response for corrections before it's reviewed.
     * Allowed: Users with review-assessments permission.
     */
    public function rejectReview(User $user, AssessmentResponse $response): bool
    {
        $assessment = $response->assessment;

        // Must be in pending_review status
        if ($response->status !== AssessmentResponseStatus::PENDING_REVIEW) {
            return false;
        }

        return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Determine if user can cancel reviewed response (reviewed → active).
     * Context: User found error after approval, needs to return to active for corrections.
     * Allowed: Users with review-assessments permission.
     */
    public function cancelReviewed(User $user, AssessmentResponse $response): bool
    {
        $assessment = $response->assessment;

        // Must be in reviewed status
        if ($response->status !== AssessmentResponseStatus::REVIEWED) {
            return false;
        }

        return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Determine if user can manage action plans for this response.
     * Action plans can only be managed when:
     * 1. Response status is 'active'
     * 2. Assessment status is 'active' or 'rejected'
     */
    public function manageActionPlan(User $user, AssessmentResponse $response): bool
    {
        $assessment = $response->assessment;

        // Cannot manage action plans if response is pending_review or reviewed
        if (in_array($response->status, [
            AssessmentResponseStatus::PENDING_REVIEW,
            AssessmentResponseStatus::REVIEWED,
        ])) {
            return false;
        }

        // Cannot manage action plans if assessment is in review, finalization, or terminal stages
        // Only allow when assessment is active or rejected (needs correction)
        $allowedAssessmentStatuses = [
            AssessmentStatus::ACTIVE->value,
            AssessmentStatus::REJECTED->value,
        ];

        if (! in_array($assessment->status->value, $allowedAssessmentStatuses)) {
            return false;
        }

        return $user->can('view-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Determine if user can manage attachments for this response.
     * Attachments can only be managed when response status is 'active'.
     */
    public function manageAttachment(User $user, AssessmentResponse $response): bool
    {
        $assessment = $response->assessment;

        // Cannot manage attachments if response is pending_review or reviewed
        if (in_array($response->status, [
            AssessmentResponseStatus::PENDING_REVIEW,
            AssessmentResponseStatus::REVIEWED,
        ])) {
            return false;
        }

        return $user->can('view-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }
}
