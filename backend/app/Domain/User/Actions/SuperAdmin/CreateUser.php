<?php

declare(strict_types=1);

namespace App\Domain\User\Actions\SuperAdmin;

use App\Domain\Organization\Models\Organization;
use App\Domain\User\Models\User;
use App\Exceptions\Domain\InvariantViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CreateUser
{
    public function execute(array $data, User $creator): User
    {
        return DB::transaction(function () use ($data, $creator) {
            // Check permission instead of isSuperAdmin() bypass
            if (!$creator->can('create-user')) {
                throw new InvariantViolationException('You do not have permission to create users.');
            }

            $organizationId = $data['organization_id'] ?? null;

            if (! $organizationId) {
                throw new InvariantViolationException('Target organization is required.');
            }

            $organization = Organization::findOrFail($organizationId);

            if (($data['role'] ?? null) === 'super_admin' && ! $organization->isMaster()) {
                throw new InvariantViolationException('Super Admin can only be created in ' . config('organization.master.name'));
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
