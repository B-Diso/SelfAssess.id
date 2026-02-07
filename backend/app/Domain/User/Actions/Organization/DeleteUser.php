<?php

namespace App\Domain\User\Actions\Organization;

use App\Domain\User\Models\User;
use App\Exceptions\Domain\InvariantViolationException;

class DeleteUser
{
    public function execute(User $user): bool
    {
        if ($user->isOrganizationAdmin()) {
            $adminCount = $user->organization->admins()->count();
            if ($adminCount <= 1) {
                throw new InvariantViolationException('Cannot delete the last Organization Admin');
            }
        }

        return $user->delete();
    }
}
