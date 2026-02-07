<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions\Compliance;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Assessment\Models\AssessmentResponse;
use Carbon\Carbon;

class GetOrganizationComplianceTrend
{
    /**
     * Get organization compliance trend.
     */
    public function handle(string $organizationId, int $months = 6): array
    {
        $labels = [];
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $labels[] = $date->format('M');

            // Calculate compliance rate for this month and organization
            $complianceRate = $this->calculateOrganizationComplianceRateForMonth($organizationId, $date);
            $data[] = $complianceRate;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Compliance Rate',
                    'data' => $data,
                ],
            ],
        ];
    }

    /**
     * Calculate compliance rate for a specific month and organization.
     */
    private function calculateOrganizationComplianceRateForMonth(string $organizationId, Carbon $date): float
    {
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        // Get assessment IDs for organization
        $assessmentIds = Assessment::where('organization_id', $organizationId)
            ->pluck('id')
            ->toArray();

        // Get responses updated in this month
        $responses = AssessmentResponse::whereIn('assessment_id', $assessmentIds)
            ->whereBetween('updated_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('compliance_status', ['not_applicable'])
            ->get();

        if ($responses->isEmpty()) {
            // Fall back to current compliance rate
            $responses = AssessmentResponse::whereIn('assessment_id', $assessmentIds)
                ->whereNotIn('compliance_status', ['not_applicable'])
                ->get();
        }

        if ($responses->isEmpty()) {
            return 0.0;
        }

        $compliantCount = $responses->whereIn('compliance_status', ['fully_compliant', 'partially_compliant'])->count();

        return round(($compliantCount / $responses->count()) * 100, 2);
    }
}
