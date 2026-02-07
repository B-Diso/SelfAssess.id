<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions\Statistics;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Organization\Models\Organization;
use App\Domain\User\Models\User;

class GetSuperAdminStatistics
{
    /**
     * Get statistics for Super Admin.
     */
    public function handle(): array
    {
        // Count active organizations
        $totalOrganizations = Organization::where('is_active', true)
            ->count();

        // Count all users (excluding soft deleted)
        $totalUsers = User::count();

        // Count active assessments (status: active, submitted, reviewed)
        $activeAssessments = Assessment::whereIn('status', ['active', 'submitted', 'reviewed'])
            ->count();

        // Count pending reviews (status: submitted)
        $pendingReviews = Assessment::where('status', 'submitted')
            ->count();

        return [
            'totalOrganizations' => $totalOrganizations,
            'totalUsers' => $totalUsers,
            'activeAssessments' => $activeAssessments,
            'pendingReviews' => $pendingReviews,
        ];
    }
}
