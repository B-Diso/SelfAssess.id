<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions\ActionPlans;

use App\Domain\Assessment\Models\AssessmentActionPlan;
use Carbon\Carbon;

class GetOrganizationActionPlans
{
    /**
     * Get organization action plans.
     */
    public function handle(string $organizationId, int $limit = 10): array
    {
        $now = Carbon::now();

        // Get overdue action plans for organization
        $overdue = AssessmentActionPlan::with(['assessment'])
            ->whereHas('assessment', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('due_date', '<', $now)
            ->orderBy('due_date', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($plan) use ($now) {
                return [
                    'id' => $plan->id,
                    'title' => $plan->title,
                    'assessment' => $plan->assessment ? [
                        'id' => $plan->assessment->id,
                        'name' => $plan->assessment->name,
                    ] : null,
                    'dueDate' => $plan->due_date?->toISOString(),
                    'daysOverdue' => $plan->due_date ? round($now->diffInDays($plan->due_date)) : 0,
                    'pic' => $plan->pic,
                ];
            })
            ->toArray();

        // Get upcoming action plans for organization
        $upcoming = AssessmentActionPlan::with(['assessment'])
            ->whereHas('assessment', function ($query) use ($organizationId) {
                $query->where('organization_id', $organizationId);
            })
            ->where('due_date', '>=', $now)
            ->where('due_date', '<=', $now->copy()->addDays(30))
            ->orderBy('due_date', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($plan) use ($now) {
                return [
                    'id' => $plan->id,
                    'title' => $plan->title,
                    'assessment' => $plan->assessment ? [
                        'id' => $plan->assessment->id,
                        'name' => $plan->assessment->name,
                    ] : null,
                    'dueDate' => $plan->due_date?->toISOString(),
                    'daysRemaining' => $plan->due_date ? round($now->diffInDays($plan->due_date, false)) : 0,
                    'pic' => $plan->pic,
                ];
            })
            ->toArray();

        return [
            'overdue' => $overdue,
            'upcoming' => $upcoming,
        ];
    }
}
