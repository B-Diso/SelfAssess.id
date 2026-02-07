<?php

namespace App\Domain\Organization\Queries;

use App\Domain\Organization\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrganizationQueryService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Organization::query();

        // Filter by specific organization ID (for non-super-admins)
        if (isset($filters['organization_id'])) {
            $query->where('id', $filters['organization_id']);
        }

        // Search by name (case-insensitive)
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where('name', 'ilike', "%{$search}%");
        }

        // Filter by active status
        if (isset($filters['isActive']) && $filters['isActive'] !== '') {
            $isActive = $filters['isActive'] === 'active' || $filters['isActive'] === 'true' || $filters['isActive'] === '1';
            $query->where('is_active', $isActive);
        }

        // Sorting with whitelist to prevent invalid columns
        $allowedSorts = ['name', 'created_at', 'updated_at'];
        $sortBy = in_array(($filters['sortBy'] ?? 'created_at'), $allowedSorts, true)
            ? $filters['sortBy']
            : 'created_at';

        $sortOrder = in_array(($filters['sortOrder'] ?? 'desc'), ['asc', 'desc'], true)
            ? $filters['sortOrder']
            : 'desc';

        $query->orderBy($sortBy, $sortOrder);

        // Pagination with validation (cap at 100 to prevent abuse)
        $perPage = min((int) ($filters['perPage'] ?? 15), 100);

        return $query->paginate($perPage);
    }
}
