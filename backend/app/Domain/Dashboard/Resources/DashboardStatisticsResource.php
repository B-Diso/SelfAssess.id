<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property array $resource
 */
class DashboardStatisticsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = $this->resource;

        return [
            'superAdmin' => isset($data['superAdmin']) ? [
                'totalOrganizations' => $data['superAdmin']['totalOrganizations'] ?? 0,
                'totalUsers' => $data['superAdmin']['totalUsers'] ?? 0,
                'activeAssessments' => $data['superAdmin']['activeAssessments'] ?? 0,
                'pendingReviews' => $data['superAdmin']['pendingReviews'] ?? 0,
            ] : null,
            'orgAdmin' => isset($data['orgAdmin']) ? [
                'totalUsers' => $data['orgAdmin']['totalUsers'] ?? 0,
                'activeAssessments' => $data['orgAdmin']['activeAssessments'] ?? 0,
                'completionRate' => $data['orgAdmin']['completionRate'] ?? 0,
                'pendingActionPlans' => $data['orgAdmin']['pendingActionPlans'] ?? 0,
            ] : null,
            'reviewer' => isset($data['reviewer']) ? [
                'pendingReviews' => $data['reviewer']['pendingReviews'] ?? 0,
                'reviewedThisMonth' => $data['reviewer']['reviewedThisMonth'] ?? 0,
                'avgReviewTime' => $data['reviewer']['avgReviewTime'] ?? 0,
                'rejectedAssessments' => $data['reviewer']['rejectedAssessments'] ?? 0,
            ] : null,
            'user' => isset($data['user']) ? [
                'myAssessments' => $data['user']['myAssessments'] ?? 0,
                'completionProgress' => $data['user']['completionProgress'] ?? 0,
                'openRequirements' => $data['user']['openRequirements'] ?? 0,
                'orgActionPlans' => $data['user']['orgActionPlans'] ?? 0,
            ] : null,
        ];
    }
}
