<?php

declare(strict_types=1);

namespace App\Domain\Report\Actions;

use App\Domain\Assessment\Models\Assessment;
use App\Domain\Organization\Models\Organization;
use App\Domain\Standard\Models\Standard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GetStandardReport
{
    /**
     * Execute the action to get standard report with stats.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<Standard>
     */
    public function execute(array $filters): LengthAwarePaginator
    {
        $search = $filters['search'] ?? null;
        $sortBy = $filters['sortBy'] ?? 'createdAt';
        $sortOrder = $filters['sortOrder'] ?? 'desc';
        $perPage = $filters['perPage'] ?? 15;
        $page = $filters['page'] ?? 1;

        // Map camelCase to snake_case for database columns
        $sortColumn = match ($sortBy) {
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
            'name' => 'name',
            'type' => 'type',
            'version' => 'version',
            default => 'created_at',
        };

        // Get the query for active standards
        $query = Standard::query()
            ->where('is_active', true)
            ->when($search, function (Builder $query, string $search) {
                $query->where(function (Builder $q) use ($search) {
                    $q->where('name', 'ilike', "%{$search}%")
                        ->orWhere('type', 'ilike', "%{$search}%")
                        ->orWhere('version', 'ilike', "%{$search}%");
                });
            })
            ->orderBy($sortColumn, $sortOrder);

        // Paginate the results
        $standards = $query->paginate($perPage, ['*'], 'page', $page);

        // Calculate stats for each standard
        $totalOrganizations = Organization::where('is_active', true)->count();

        $standards->getCollection()->transform(function (Standard $standard) use ($totalOrganizations) {
            $startedOrganizations = Assessment::where('standard_id', $standard->id)
                ->distinct('organization_id')
                ->count('organization_id');

            $standard->stats = [
                'totalOrganizations' => $totalOrganizations,
                'startedOrganizations' => $startedOrganizations,
                'notStartedOrganizations' => $totalOrganizations - $startedOrganizations,
            ];

            return $standard;
        });

        return $standards;
    }
}
