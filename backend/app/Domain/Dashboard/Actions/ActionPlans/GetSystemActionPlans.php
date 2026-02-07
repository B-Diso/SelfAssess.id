<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions\ActionPlans;

use App\Domain\Assessment\Models\AssessmentActionPlan;
use Carbon\Carbon;

class GetSystemActionPlans
{
    /**
     * Get system-wide action plans.
     */
    public function handle(int $limit = 10): array
    {
        $now = Carbon::now();

        // Get overdue action plans
        $overdue = AssessmentActionPlan::with(['assessment.organization'])
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
                    'organization' => $plan->assessment?->organization?->name,
                    'dueDate' => $plan->due_date?->toISOString(),
                    'daysOverdue' => $plan->due_date ? round($now->diffInDays($plan->due_date)) : 0,
                    'pic' => $plan->pic,
                ];
            })
            ->toArray();

        // Get upcoming action plans (due within next 30 days)
        $upcoming = AssessmentActionPlan::with(['assessment.organization'])
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
                    'organization' => $plan->assessment?->organization?->name,
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
