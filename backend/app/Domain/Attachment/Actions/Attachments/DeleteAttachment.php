<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Actions\Attachments;

use App\Domain\Attachment\Models\Attachment;
use Illuminate\Support\Facades\Storage;

class DeleteAttachment
{
    /**
     * Handle deleting the attachment.
     */
    public function handle(Attachment $attachment): bool
    {
        // Actually we might want to keep the file if there's soft delete, 
        // but typically soft delete models don't delete the physical file until force deletion.
        // For now, let's just use soft delete.
        return $attachment->delete();
    }
}
