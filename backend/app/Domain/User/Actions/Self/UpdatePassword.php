<?php

namespace App\Domain\User\Actions\Self;

use App\Domain\User\Models\User;
use App\Exceptions\Domain\InvariantViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdatePassword
{
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (! Hash::check($data['current_password'], $user->password)) {
                throw new InvariantViolationException('Current password is incorrect.');
            }

            if (Hash::check($data['new_password'], $user->password)) {
                throw new InvariantViolationException('New password must be different from the current password.');
            }

            $user->forceFill([
                'password' => Hash::make($data['new_password']),
            ])->save();

            return $user->fresh();
        });
    }
}
