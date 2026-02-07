<?php

namespace App\Domain\User\Actions\Organization;

use App\Domain\User\Models\User;

class AssignRole
{
    public function execute(User $user, string $role): User
    {
        $user->assignRole($role);

        return $user->load('organization', 'roles');
    }
}
