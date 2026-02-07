<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Actions\ActionPlans;

use App\Domain\Assessment\Models\AssessmentActionPlan;
use App\Domain\User\Models\User;
use Carbon\Carbon;

class GetUserActionPlans
{
    /**
     * Get user action plans.
     */
    public function handle(string $userId, int $limit = 10): array
    {
        $user = User::find($userId);
        if (! $user) {
            return [
                'overdue' => [],
                'upcoming' => [],
            ];
        }

        $now = Carbon::now();

        // Get overdue action plans for user (where user is PIC)
        $overdue = AssessmentActionPlan::with(['assessment'])
            ->where('pic', 'like', '%'.$user->name.'%')
            ->whereHas('assessment', function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
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

        // Get upcoming action plans for user
        $upcoming = AssessmentActionPlan::with(['assessment'])
            ->where('pic', 'like', '%'.$user->name.'%')
            ->whereHas('assessment', function ($query) use ($user) {
                $query->where('organization_id', $user->organization_id);
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
