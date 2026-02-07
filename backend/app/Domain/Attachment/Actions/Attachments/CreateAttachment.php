<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Actions\Attachments;

use App\Domain\Attachment\Models\Attachment;
use App\Services\FileUploadValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CreateAttachment
{
    public function __construct(
        protected FileUploadValidator $fileValidator
    ) {
    }

    /**
     * Handle the attachment creation.
     *
     * Security measures:
     * - File has already been validated through FileUploadValidator in the FormRequest
     * - Uses secure random filename (hashName) to prevent RCE and path traversal
     * - Stores files outside webroot (storage/app)
     * - Sanitizes original filename for display purposes
     * - Logs file creation for audit trail
     */
    public function handle(array $data, UploadedFile $file, string $userId): Attachment
    {
        $disk = 'local';
        $organizationId = $data['organization_id'];

        // Use hashName() for a secure, random filename to prevent RCE and path traversal
        // This generates a SHA-256 hash based filename with the original extension
        $storedName = $file->hashName();

        // Sanitize category to prevent directory traversal
        $category = $this->sanitizeCategory($data['category'] ?? 'uncategorized');

        // Build secure path
        $path = "attachments/{$organizationId}/{$category}/{$storedName}";

        // Store the file
        Storage::disk($disk)->putFileAs(
            dirname($path),
            $file,
            basename($path)
        );

        // Sanitize the original filename for display (not storage)
        $sanitizedName = $this->fileValidator->sanitizeFilename($file->getClientOriginalName());

        // Create the attachment record
        $attachment = Attachment::create([
            'id' => (string) Str::uuid7(),
            'organization_id' => $organizationId,
            'name' => $sanitizedName,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'path' => $path,
            'disk' => $disk,
            'category' => $data['category'] ?? null,
            'description' => $data['description'] ?? null,
            'created_by_id' => $userId,
            'updated_by_id' => $userId,
        ]);

        // Log successful file creation for audit trail
        Log::info('Attachment created', [
            'attachment_id' => $attachment->id,
            'organization_id' => $organizationId,
            'original_filename' => $sanitizedName,
            'stored_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'user_id' => $userId,
        ]);

        return $attachment;
    }

    /**
     * Sanitize category name to prevent directory traversal attacks.
     */
    protected function sanitizeCategory(string $category): string
    {
        // Remove any directory separators and dangerous characters
        $category = str_replace(['..', '/', '\\', "\0"], '', $category);

        // Convert to slug format
        $category = Str::slug($category, '_');

        // Ensure category is not empty after sanitization
        if (empty($category)) {
            $category = 'uncategorized';
        }

        return $category;
    }
}
