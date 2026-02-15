<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Policies;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\User\Models\User;

class AssessmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view-assessments');
    }

    public function view(User $user, Assessment $assessment): bool
    {
        return $user->can('view-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    public function create(User $user): bool
    {
        // Only Super Admin and Organization Admin can create assessments
        // Using permission check - super_admin has all permissions via role
        return $user->can('review-assessments');
    }

    public function update(User $user, Assessment $assessment): bool
    {
        // Only finished and cancelled assessments are completely immutable
        // Organization admins can edit basic details (name, dates) in all other statuses
        if (in_array($assessment->status->value, [
            AssessmentStatus::FINISHED->value,
            AssessmentStatus::CANCELLED->value,
        ])) {
            return false;
        }

        // Only users with review-assessments permission can edit assessment details
        return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    public function delete(User $user, Assessment $assessment): bool
    {
        // Only draft and active assessments can be deleted
        $allowedStatuses = [
            AssessmentStatus::DRAFT->value,
            AssessmentStatus::ACTIVE->value,
        ];

        if (! in_array($assessment->status->value, $allowedStatuses)) {
            return false;
        }

       // Check review-assessments permission and organization access
       return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);

    }

    /**
     * Determine if user can create action plans for this assessment.
     * Action plans can be created in all non-finalized statuses.
     */
    public function createActionPlan(User $user, Assessment $assessment): bool
    {
        // Cannot create action plans for finished or cancelled assessments
        if (in_array($assessment->status, [
            AssessmentStatus::FINISHED,
            AssessmentStatus::CANCELLED,
        ])) {
            return false;
        }

        return $this->view($user, $assessment);
    }

    /**
     * Determine if assessment can be transitioned to a new status.
     * Delegates to specific transition methods based on from/to status.
     */
    public function transition(User $user, Assessment $assessment, string $toStatus): bool
    {

        return match ($toStatus) {
            AssessmentStatus::ACTIVE->value => $this->canActivate($user, $assessment),
            AssessmentStatus::PENDING_REVIEW->value => $this->canSubmitForReview($user, $assessment),
            AssessmentStatus::REVIEWED->value => $this->canApproveReview($user, $assessment),
            AssessmentStatus::PENDING_FINISH->value => $this->canRequestFinish($user, $assessment),
            AssessmentStatus::FINISHED->value => $this->canFinalize($user, $assessment),
            AssessmentStatus::REJECTED->value => $this->canRejectAssessment($user, $assessment),
            AssessmentStatus::CANCELLED->value => $this->canCancel($user, $assessment),
            default => false,
        };
    }

    /**
     * Check if user can activate/reactivate assessment.
     * Allowed from: draft, cancelled, rejected
     * Only users with review-assessments permission.
     */
    public function canActivate(User $user, Assessment $assessment): bool
    {
        // Can activate from: draft, cancelled, rejected
        $canActivateFrom = [
            AssessmentStatus::DRAFT->value,
            AssessmentStatus::CANCELLED->value,
            AssessmentStatus::REJECTED->value,
        ];

        if (! in_array($assessment->status->value, $canActivateFrom)) {
            return false;
        }

        return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Check if user can submit assessment for review (active → pending_review).
     * Users with review-assessments permission can submit for review.
     */
    public function canSubmitForReview(User $user, Assessment $assessment): bool
    {
        if ($assessment->status !== AssessmentStatus::ACTIVE->value) {
            return false;
        }

        return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Check if user can approve review (pending_review → reviewed).
     * Users with review-assessments permission can approve reviews.
     */
    public function canApproveReview(User $user, Assessment $assessment): bool
    {
        return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Check if user can reject review (pending_review → active).
     * Users with review-assessments permission can reject reviews.
     */
    public function canRejectReview(User $user, Assessment $assessment): bool
    {
        return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Check if user can request finish (reviewed → pending_finish).
     * Users with review-assessments permission can request finish.
     */
    public function canRequestFinish(User $user, Assessment $assessment): bool
    {
        if ($assessment->status !== AssessmentStatus::REVIEWED->value) {
            return false;
        }

        return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Check if user can revert from reviewed (reviewed → active).
     * Users with review-assessments permission can revert from reviewed.
     */
    public function canRevertFromReviewed(User $user, Assessment $assessment): bool
    {
        if ($assessment->status !== AssessmentStatus::REVIEWED->value) {
            return false;
        }

        return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Check if user can finalize assessment (pending_finish → finished).
     * Users with review-assessments permission can finalize.
     */
    public function canFinalize(User $user, Assessment $assessment): bool
    {
        if ($assessment->status !== AssessmentStatus::PENDING_FINISH->value) {
            return false;
        }

        return $user->can('review-assessments');
    }

    /**
     * Check if user can revert from pending finish (pending_finish → active).
     * Users with review-assessments permission can revert from pending finish.
     */
    public function canRevertFromPendingFinish(User $user, Assessment $assessment): bool
    {
        if ($assessment->status !== AssessmentStatus::PENDING_FINISH->value) {
            return false;
        }

        return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Check if user can reject assessment (reviewed/pending_finish → rejected).
     * Users with review-assessments permission can reject assessments.
     */
    public function canRejectAssessment(User $user, Assessment $assessment): bool
    {
        // Can reject from: pending_review, reviewed, pending_finish
        $canRejectFrom = [
            AssessmentStatus::PENDING_REVIEW->value,
            AssessmentStatus::REVIEWED->value,
            AssessmentStatus::PENDING_FINISH->value,
        ];

        if (! in_array($assessment->status->value, $canRejectFrom)) {
            return false;
        }

        return $user->can('review-assessments') && $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Check if user can cancel assessment (any status → cancelled).
     * Users with review-assessments permission can cancel.
     */
    public function canCancel(User $user, Assessment $assessment): bool
    {
        if ($assessment->status === AssessmentStatus::FINISHED->value) {
            return false;
        }

        return $user->can('review-assessments');
    }
}
