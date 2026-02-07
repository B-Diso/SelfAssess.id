<?php

namespace App\Domain\Role\Actions\SuperAdmin;

use App\Exceptions\Domain\InvariantViolationException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UpdateRole
{
    public function execute(Role $role, array $data): Role
    {
        // Guard against modifying system roles
        if ($role->is_system) {
            throw new InvariantViolationException('Cannot modify system roles');
        }

        return DB::transaction(function () use ($role, $data) {
            if (isset($data['name'])) {
                $role->update(['name' => $data['name']]);
            }

            if (isset($data['permissions'])) {
                $role->syncPermissions($data['permissions']);
            }

            return $role->load('permissions');
        });
    }
}
