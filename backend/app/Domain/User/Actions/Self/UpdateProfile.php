<?php

namespace App\Domain\User\Actions\Self;

use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateProfile
{
    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $payload = array_filter([
                'name' => $data['name'] ?? null,
                'email' => $data['email'] ?? null,
            ], static fn ($value) => ! is_null($value));

            if (empty($payload)) {
                return $user->load('organization', 'roles');
            }

            $user->fill($payload);
            $user->save();

            return $user->fresh(['organization', 'roles']);
        });
    }
}
