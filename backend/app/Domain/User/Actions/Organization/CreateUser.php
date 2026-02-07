<?php

namespace App\Domain\User\Actions\Organization;

use App\Domain\User\Models\User;
use App\Exceptions\Domain\InvariantViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUser
{
    public function execute(array $data, User $creator): User
    {
        return DB::transaction(function () use ($data, $creator) {
            $organizationId = $creator->organization_id;

            if (($data['role'] ?? null) === 'super_admin') {
                throw new InvariantViolationException('Super Admin role cannot be assigned through organization-scoped endpoint.');
            }

            $user = User::create([
                'organization_id' => $organizationId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->assignRole($data['role']);

            return $user->load('organization', 'roles');
        });
    }
}
