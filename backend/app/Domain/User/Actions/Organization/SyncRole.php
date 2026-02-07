<?php

namespace App\Domain\User\Actions\Organization;

use App\Domain\User\Models\User;

class SyncRole
{
    public function execute(User $user, string $role): User
    {
        $user->syncRoles([$role]);

        return $user->load('organization', 'roles');
    }
}
