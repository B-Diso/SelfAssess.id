<?php

namespace App\Domain\User\Actions\SuperAdmin;

use App\Domain\Organization\Models\Organization;
use App\Domain\User\Models\User;
use App\Exceptions\Domain\InvariantViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferUser
{
    public function execute(User $user, string $targetOrgId, ?string $reason, User $transferredBy): User
    {
        return DB::transaction(function () use ($user, $targetOrgId, $reason, $transferredBy) {
            $oldOrg = $user->organization;

            if ($user->isSuperAdmin()) {
                throw new InvariantViolationException('Cannot transfer Super Admin from this Organization');
            }

            if ($user->isOrganizationAdmin()) {
                $adminCount = $oldOrg->admins()->count();
                if ($adminCount <= 1) {
                    throw new InvariantViolationException('Cannot transfer the last Organization Admin');
                }
            }

            $targetOrg = Organization::findOrFail($targetOrgId);

            $user->update(['organization_id' => $targetOrgId]);

            $currentRoles = $user->getRoleNames();
            $user->syncRoles($currentRoles);

            $this->logTransfer($user, $oldOrg, $targetOrg, $transferredBy, $reason);

            auth()->invalidate(true);

            return $user->fresh(['organization', 'roles']);
        });
    }

    protected function logTransfer(User $user, Organization $fromOrg, Organization $toOrg, User $transferredBy, ?string $reason): void
    {
        Log::channel('transfer')->info('User transferred', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'from_organization_id' => $fromOrg->id,
            'from_organization_name' => $fromOrg->name,
            'to_organization_id' => $toOrg->id,
            'to_organization_name' => $toOrg->name,
            'transferred_by' => $transferredBy->id,
            'transferred_by_email' => $transferredBy->email,
            'reason' => $reason,
            'transferred_at' => now()->toIso8601String(),
            'ip_address' => request()->ip(),
        ]);
    }
}
