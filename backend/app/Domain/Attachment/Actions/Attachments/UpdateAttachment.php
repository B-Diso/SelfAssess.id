<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Actions\Attachments;

use App\Domain\Attachment\Models\Attachment;

class UpdateAttachment
{
    /**
     * Handle updating attachment metadata.
     */
    public function handle(Attachment $attachment, array $data, string $userId): Attachment
    {
        $attachment->fill($data);
        $attachment->updated_by_id = $userId;
        $attachment->save();

        return $attachment;
    }
}
