<?php

namespace App\Domain\Role\Actions\SuperAdmin;

use App\Exceptions\Domain\InvariantViolationException;
use Spatie\Permission\Models\Role;

class DeleteRole
{
    public function execute(Role $role): bool
    {
        // Guard against deleting system roles
        if ($role->is_system) {
            throw new InvariantViolationException('Cannot delete system roles');
        }

        return $role->delete();
    }
}
