<?php

namespace App\Domain\Standard\Queries;

use App\Domain\Standard\Models\Standard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class StandardQueryService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Standard::query();

        $this->applyFilters($query, $filters);

        $perPage = $filters['perPage'] ?? 15;

        return $query->paginate($perPage);
    }

    public function find(string $id): ?Standard
    {
        return Standard::find($id);
    }

    protected function applyFilters(Builder $query, array $filters): void
    {
        // Search by name or version (case-insensitive)
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('version', 'ilike', "%{$search}%");
            });
        }

        // Filter by type
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        // Filter by active status
        if (isset($filters['isActive']) && $filters['isActive'] !== 'all' && $filters['isActive'] !== 'undefined') {
            $isActive = $filters['isActive'] === 'true' || $filters['isActive'] === '1' || $filters['isActive'] === true;
            $query->where('is_active', $isActive);
        }

        // Sorting - map camelCase to snake_case for database
        $sortBy = $filters['sortBy'] ?? 'created_at';
        $sortOrder = $filters['sortOrder'] ?? 'desc';

        // Map camelCase sort fields to database columns
        $sortMap = [
            'name' => 'name',
            'version' => 'version',
            'type' => 'type',
            'status' => 'is_active',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
        ];

        $dbSortBy = $sortMap[$sortBy] ?? 'created_at';
        $query->orderBy($dbSortBy, $sortOrder);
    }
}
