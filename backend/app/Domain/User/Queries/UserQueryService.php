<?php

namespace App\Domain\User\Queries;

use App\Domain\User\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UserQueryService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = User::with(['organization', 'roles']);

        // Scope by organization if provided
        if (isset($filters['organization_id'])) {
            $query->where('organization_id', $filters['organization_id']);
        }

        // Search by name, email, or organization (case-insensitive)
        if (isset($filters['search']) && $filters['search']) {
            $search = strtolower($filters['search']);
            $query->where(function (Builder $q) use ($search) {
                $q->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                  ->orWhereHas('organization', function (Builder $sq) use ($search) {
                      $sq->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                  });
            });
        }

        // Filter by role
        if (isset($filters['role']) && $filters['role']) {
            $query->whereHas('roles', function (Builder $q) use ($filters) {
                $q->where('name', $filters['role']);
            });
        }

        // Sorting with whitelist and mapping
        $sortBy = $filters['sortBy'] ?? 'createdAt';
        $sortOrder = in_array(($filters['sortOrder'] ?? 'desc'), ['asc', 'desc'], true)
            ? $filters['sortOrder']
            : 'desc';

        $sortMap = [
            'name' => 'name',
            'email' => 'email',
            'createdAt' => 'created_at',
            'updatedAt' => 'updated_at',
            'organizationName' => 'organization_name',
        ];

        $dbSortBy = $sortMap[$sortBy] ?? 'created_at';

        if ($dbSortBy === 'organization_name') {
            $query->join('organizations', 'users.organization_id', '=', 'organizations.id')
                  ->orderBy('organizations.name', $sortOrder)
                  ->select('users.*');
        } else {
            $query->orderBy($dbSortBy, $sortOrder);
        }

        // Pagination with validation (cap at 100 to prevent abuse)
        $perPage = min((int) ($filters['perPage'] ?? 15), 100);

        return $query->paginate($perPage);
    }
}
