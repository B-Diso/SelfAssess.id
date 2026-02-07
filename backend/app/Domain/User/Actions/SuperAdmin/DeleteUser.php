<?php

namespace App\Domain\User\Actions\SuperAdmin;

use App\Domain\User\Actions\Organization\DeleteUser as OrganizationDeleteUser;
use App\Domain\User\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUser
{
    public function __construct(protected OrganizationDeleteUser $organizationDeleteUser)
    {
    }

    public function execute(User $user): bool
    {
        return DB::transaction(fn () => $this->organizationDeleteUser->execute($user));
    }
}
