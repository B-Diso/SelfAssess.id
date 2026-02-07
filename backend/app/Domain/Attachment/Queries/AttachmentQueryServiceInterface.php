<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Queries;

use App\Domain\Attachment\Models\Attachment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface AttachmentQueryServiceInterface
{
    /**
     * Get attachments with filtering, search, and pagination.
     */
    public function getAttachments(Request $request): LengthAwarePaginator;

    /**
     * Get a single attachment by ID with creator relation loaded.
     */
    public function getAttachmentById(string $id): Attachment;

    /**
     * Check if an attachment exists by ID.
     */
    public function attachmentExists(string $id): bool;
}
