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
        return $user->exists;
    }

    public function view(User $user, Assessment $assessment): bool
    {
        return $user->isSuperAdmin() || $user->canAccessOrganization($assessment->organization_id);
    }

    public function create(User $user): bool
    {
        // Only Super Admin and Organization Admin can create assessments
        return $user->isSuperAdmin() || $user->isOrganizationAdmin();
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

        // Only org admins and super admins can edit assessment details
        return $user->isSuperAdmin() || $user->isOrganizationAdmin();
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

        // Super Admin can delete any draft/active assessment
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Organization Admin can only delete assessments from their own organization
        if ($user->isOrganizationAdmin()) {
            return $user->organization_id === $assessment->organization_id;
        }

        return false;
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
        // Super Admin has access to all transitions
        if ($user->isSuperAdmin()) {
            return true;
        }

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
     * Only Organization Admin can activate assessments.
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

        return $user->isSuperAdmin() || $user->isOrganizationAdmin();
    }

    /**
     * Check if user can submit assessment for review (active → pending_review).
     * Organization Users and Organization Admins can submit for review.
     */
    public function canSubmitForReview(User $user, Assessment $assessment): bool
    {
        if ($assessment->status !== AssessmentStatus::ACTIVE->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Check if user can approve review (pending_review → reviewed).
     * Organization Admin and Super Admin can approve reviews.
     */
    public function canApproveReview(User $user, Assessment $assessment): bool
    {
        return $user->isSuperAdmin() || $user->isOrganizationAdmin();
    }

    /**
     * Check if user can reject review (pending_review → active).
     * Organization Admin and Super Admin can reject reviews.
     */
    public function canRejectReview(User $user, Assessment $assessment): bool
    {
        return $user->isSuperAdmin() || $user->isOrganizationAdmin();
    }

    /**
     * Check if user can request finish (reviewed → pending_finish).
     * Organization Admin and Super Admin can request finish.
     */
    public function canRequestFinish(User $user, Assessment $assessment): bool
    {
        if ($assessment->status !== AssessmentStatus::REVIEWED->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->isOrganizationAdmin();
    }

    /**
     * Check if user can revert from reviewed (reviewed → active).
     * Organization User, Organization Admin, and Super Admin can revert from reviewed.
     */
    public function canRevertFromReviewed(User $user, Assessment $assessment): bool
    {
        if ($assessment->status !== AssessmentStatus::REVIEWED->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Check if user can finalize assessment (pending_finish → finished).
     * Only Super Admin can finalize assessments.
     */
    public function canFinalize(User $user, Assessment $assessment): bool
    {
        if ($assessment->status !== AssessmentStatus::PENDING_FINISH->value) {
            return false;
        }

        return $user->isSuperAdmin();
    }

    /**
     * Check if user can revert from pending finish (pending_finish → active).
     * Organization User, Organization Admin, and Super Admin can revert from pending finish.
     */
    public function canRevertFromPendingFinish(User $user, Assessment $assessment): bool
    {
        if ($assessment->status !== AssessmentStatus::PENDING_FINISH->value) {
            return false;
        }

        return $user->isSuperAdmin() || $user->canAccessOrganization($assessment->organization_id);
    }

    /**
     * Check if user can reject assessment (reviewed/pending_finish → rejected).
     * Organization Admin and Super Admin can reject assessments.
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

        return $user->isSuperAdmin() || $user->isOrganizationAdmin();
    }

    /**
     * Check if user can cancel assessment (any status → cancelled).
     * Only Super Admin can cancel assessments.
     */
    public function canCancel(User $user, Assessment $assessment): bool
    {
        if ($assessment->status === AssessmentStatus::FINISHED->value) {
            return false;
        }

        return $user->isSuperAdmin();
    }
}
