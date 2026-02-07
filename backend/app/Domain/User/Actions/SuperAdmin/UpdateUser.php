<?php

namespace App\Domain\User\Actions\SuperAdmin;

use App\Domain\User\Models\User;
use App\Exceptions\Domain\InvariantViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateUser
{
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['organization_id']) && $data['organization_id'] !== $user->organization_id) {
                throw new InvariantViolationException('Use transfer endpoint to change organization.');
            }

            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $role = $data['role'] ?? null;

            unset($data['role'], $data['organization_id']);

            if (! empty($data)) {
                $user->update($data);
            }

            if ($role !== null) {
                $organization = $user->organization()->first();

                if ($role === 'super_admin' && (! $organization || ! $organization->isMaster())) {
                    throw new InvariantViolationException('Super Admin can only belong to ' . config('organization.master.name'));
                }

                $user->syncRoles([$role]);
            }

            return $user->fresh(['organization', 'roles']);
        });
    }
}
