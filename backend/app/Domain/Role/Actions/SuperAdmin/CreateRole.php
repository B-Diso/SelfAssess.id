<?php

namespace App\Domain\Role\Actions\SuperAdmin;

use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class CreateRole
{
    public function execute(array $data): Role
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'guard_name' => $data['guardName'] ?? 'api',
            ]);

            if (!empty($data['permissions'])) {
                $role->givePermissionTo($data['permissions']);
            }

            return $role->load('permissions');
        });
    }
}
