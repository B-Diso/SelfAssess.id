<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Policies;

use App\Domain\Attachment\Models\Attachment;
use App\Domain\User\Models\User;

class AttachmentPolicy
{

    /**
     * Determine whether the user can view any attachments.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view-attachments');
    }

    /**
     * Determine whether the user can view the attachment.
     */
    public function view(User $user, Attachment $attachment): bool
    {
        return $user->organization_id === $attachment->organization_id && $user->can('view-attachments');
    }

    /**
     * Determine whether the user can create attachments.
     */
    public function create(User $user): bool
    {
        return $user->can('view-attachments');
    }

    /**
     * Determine whether the user can update the attachment.
     */
    public function update(User $user, Attachment $attachment): bool
    {
        return $user->organization_id === $attachment->organization_id && $user->can('view-attachments');
    }

    /**
     * Determine whether the user can delete the attachment.
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        return $user->organization_id === $attachment->organization_id && $user->can('view-attachments');
    }
}
