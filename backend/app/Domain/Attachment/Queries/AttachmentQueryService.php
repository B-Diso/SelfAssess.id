<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Queries;

use App\Domain\Attachment\Models\Attachment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AttachmentQueryService implements AttachmentQueryServiceInterface
{
    /**
     * Get attachments with filtering, search, and pagination.
     */
    public function getAttachments(Request $request): LengthAwarePaginator
    {
        $query = Attachment::query()
            ->with(['creator'])
            ->orderByDesc('created_at');

        // Organization filtering based on user role
        if (!$request->user()->isSuperAdmin()) {
            $query->where('organization_id', $request->user()->organization_id);
        } elseif ($request->filled('organization_id')) {
            $query->where('organization_id', $request->get('organization_id'));
        }

        // Category filtering
        if ($request->filled('category')) {
            $query->where('category', $request->get('category'));
        }

        // Search by name or description (ILIKE for PostgreSQL)
        if (!empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('description', 'ILIKE', "%{$search}%");
            });
        }

        // Pagination with validation (cap at 100 to prevent abuse)
        $perPage = min((int) ($request->integer('perPage', 15)), 100);

        return $query->paginate($perPage);
    }

    /**
     * Get a single attachment by ID with creator relation loaded.
     */
    public function getAttachmentById(string $id): Attachment
    {
        return Attachment::with(['creator'])->findOrFail($id);
    }

    /**
     * Check if an attachment exists by ID.
     */
    public function attachmentExists(string $id): bool
    {
        return Attachment::where('id', $id)->exists();
    }
}
