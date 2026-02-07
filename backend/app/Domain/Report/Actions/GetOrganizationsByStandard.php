<?php

declare(strict_types=1);

namespace App\Domain\Report\Actions;

use App\Domain\Assessment\Enums\AssessmentStatus;
use App\Domain\Assessment\Models\Assessment;
use App\Domain\Organization\Models\Organization;
use App\Domain\Standard\Models\Standard;
use Illuminate\Support\Collection;

class GetOrganizationsByStandard
{
    /**
     * Get organizations with their assessment info for a specific standard.
     *
     * Supports search, sort, and pagination.
     *
     * @param Standard $standard The standard to get organizations for
     * @param array<string, mixed> $filters Filter parameters:
     *   - search: string|null - Filter by organization name (case-insensitive)
     *   - sortBy: string|null - Sort field: name|startedAt|progress|status
     *   - sortOrder: string|null - Sort direction: asc|desc
     *   - page: int - Current page number (default: 1)
     *   - perPage: int - Items per page (default: 15)
     * @return array<string, mixed> Paginated result with data and meta
     */
    public function execute(Standard $standard, array $filters = []): array
    {
        $search = $filters['search'] ?? null;
        $sortBy = $filters['sortBy'] ?? null;
        $sortOrder = strtolower($filters['sortOrder'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['perPage'] ?? 15);

        // Get all organizations ordered by name (for consistent ordering when no sort specified)
        $organizationsQuery = Organization::query();

        // Apply search filter
        if ($search !== null && $search !== '') {
            $organizationsQuery->where('name', 'ilike', '%' . $search . '%');
        }

        $organizations = $organizationsQuery->get();

        // Get latest assessment for each organization for this standard
        // We need to get the most recent assessment (by created_at) per organization
        $latestAssessments = Assessment::query()
            ->where('standard_id', $standard->id)
            ->where('status', '!=', AssessmentStatus::DRAFT->value)
            ->with('responses')
            ->orderBy('created_at', 'desc')
            ->get()
            // Group by organization and take the first (most recent) for each
            ->groupBy('organization_id')
            ->map(fn($assessments) => $assessments->first());

        // Build the result
        $items = $organizations
            ->map(function (Organization $organization) use ($latestAssessments) {
                $assessment = $latestAssessments->get($organization->id);

                return [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'hasAssessment' => $assessment !== null,
                    'assessmentId' => $assessment?->id,
                    'assessmentStatus' => $assessment?->status,
                    'assessmentProgress' => $assessment ? $this->calculateProgress($assessment) : null,
                    'startedAt' => $assessment?->created_at?->toIso8601String(),
                ];
            });

        // Apply sorting
        $items = $this->applySorting($items, $sortBy, $sortOrder);

        // Apply pagination
        return $this->paginate($items, $page, $perPage);
    }

    /**
     * Apply sorting to the collection.
     *
     * @param Collection<int, array<string, mixed>> $items
     * @param string|null $sortBy
     * @param string $sortOrder
     * @return Collection<int, array<string, mixed>>
     */
    private function applySorting(Collection $items, ?string $sortBy, string $sortOrder): Collection
    {
        if ($sortBy === null) {
            // Default sort: Organizations WITH assessments first, then by name
            return $items
                ->sortBy(function ($item) {
                    return [$item['hasAssessment'] ? 0 : 1, $item['name']];
                })
                ->values();
        }

        $sorted = match ($sortBy) {
            'name' => $items->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE),
            'startedAt' => $items->sortBy(function ($item) {
                // Nulls last: use a large timestamp for null values
                return $item['startedAt'] ?? '9999-12-31T23:59:59Z';
            }),
            'progress' => $items->sortBy(function ($item) {
                // Nulls last: use -1 for null so they appear after valid progress values
                return $item['assessmentProgress'] ?? -1;
            }),
            'status' => $items->sortBy(function ($item) {
                // Custom order: finished > reviewed > submitted > active > rejected > cancelled > draft > null
                $order = [
                    'finished' => 1,
                    'reviewed' => 2,
                    'submitted' => 3,
                    'active' => 4,
                    'rejected' => 5,
                    'cancelled' => 6,
                    'draft' => 7,
                ];
                $status = $item['assessmentStatus'];
                return $status !== null ? ($order[$status] ?? 8) : 9;
            }),
            default => $items->sortBy(function ($item) {
                // Default: Organizations WITH assessments first, then by name
                return [$item['hasAssessment'] ? 0 : 1, $item['name']];
            }),
        };

        // Apply sort order
        if ($sortOrder === 'desc') {
            $sorted = $sorted->reverse();
        }

        return $sorted->values();
    }

    /**
     * Paginate the collection.
     *
     * @param Collection<int, array<string, mixed>> $items
     * @param int $page
     * @param int $perPage
     * @return array<string, mixed>
     */
    private function paginate(Collection $items, int $page, int $perPage): array
    {
        $total = $items->count();
        $lastPage = (int) ceil($total / $perPage) ?: 1;
        
        // Ensure page is within valid range
        $page = max(1, min($page, $lastPage));
        
        $offset = ($page - 1) * $perPage;
        $paginatedItems = $items->slice($offset, $perPage)->values();

        return [
            'data' => $paginatedItems,
            'meta' => [
                'currentPage' => $page,
                'lastPage' => $lastPage,
                'total' => $total,
                'perPage' => $perPage,
            ],
        ];
    }

    /**
     * Calculate assessment progress based on response statuses.
     * Each requirement contributes:
     * - active = 0%
     * - pending_review = 50%
     * - reviewed = 100%
     */
    private function calculateProgress(Assessment $assessment): int
    {
        // Load responses if not already loaded
        if (!$assessment->relationLoaded('responses')) {
            $assessment->load('responses');
        }

        $responses = $assessment->responses;

        if ($responses->isEmpty()) {
            return 0;
        }

        $totalScore = 0;
        foreach ($responses as $response) {
            $totalScore += match ($response->status->value) {
                'reviewed' => 100,
                'pending_review' => 50,
                default => 0, // active
            };
        }

        return (int) round($totalScore / $responses->count());
    }
}
