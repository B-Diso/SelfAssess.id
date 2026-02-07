<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class FileUploadValidator
{
    /**
     * Validate a file upload against security requirements.
     *
     * @param UploadedFile $file The uploaded file to validate
     * @param string|null $customErrorMessage Custom error message to use
     * @return array{valid: bool, error?: string} Validation result
     */
    public function validate(UploadedFile $file, ?string $customErrorMessage = null): array
    {
        try {
            // Validate file size
            if (!$this->validateFileSize($file)) {
                return $this->rejected(
                    $file,
                    'file_size',
                    $customErrorMessage ?? 'File size exceeds maximum allowed size'
                );
            }

            // Validate file extension
            $extensionResult = $this->validateExtension($file);
            if (!$extensionResult['valid']) {
                return $extensionResult;
            }

            // Validate MIME type from actual file content
            $mimeTypeResult = $this->validateMimeType($file);
            if (!$mimeTypeResult['valid']) {
                return $mimeTypeResult;
            }

            // Validate MIME type matches extension
            $mimeTypeMatchResult = $this->validateMimeTypeMatchesExtension($file);
            if (!$mimeTypeMatchResult['valid']) {
                return $mimeTypeMatchResult;
            }

            // Check for double extensions
            if (Config::get('upload.security.check_double_extensions', true)) {
                $doubleExtResult = $this->validateNoDoubleExtensions($file);
                if (!$doubleExtResult['valid']) {
                    return $doubleExtResult;
                }
            }

            return ['valid' => true];
        } catch (InvalidArgumentException $e) {
            return $this->rejected($file, 'validation_error', $e->getMessage());
        } catch (\Exception $e) {
            Log::error('File validation error', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
            ]);

            return $this->rejected($file, 'system_error', 'File validation failed');
        }
    }

    /**
     * Validate the file size does not exceed maximum.
     */
    protected function validateFileSize(UploadedFile $file): bool
    {
        $maxSize = Config::get('upload.max_file_size', 2 * 1024 * 1024);
        $fileSize = $file->getSize();

        if ($fileSize === null || $fileSize === false) {
            throw new InvalidArgumentException('Cannot determine file size');
        }

        return $fileSize <= $maxSize;
    }

    /**
     * Validate the file extension is allowed.
     */
    protected function validateExtension(UploadedFile $file): array
    {
        $extension = Str::lower($file->getClientOriginalExtension());
        $allowedExtensions = Config::get('upload.allowed_extensions', []);

        if (empty($extension)) {
            return $this->rejected($file, 'invalid_extension', 'File has no extension');
        }

        if (!in_array($extension, $allowedExtensions, true)) {
            return $this->rejected(
                $file,
                'invalid_extension',
                sprintf('File extension ".%s" is not allowed', $extension)
            );
        }

        return ['valid' => true];
    }

    /**
     * Validate the MIME type from actual file content.
     */
    protected function validateMimeType(UploadedFile $file): array
    {
        $mimeType = $file->getMimeType();
        $allowedMimeTypes = Config::get('upload.allowed_mime_types', []);

        if ($mimeType === null || $mimeType === false) {
            return $this->rejected($file, 'invalid_mime_type', 'Cannot determine file MIME type');
        }

        if (!in_array($mimeType, $allowedMimeTypes, true)) {
            return $this->rejected(
                $file,
                'invalid_mime_type',
                sprintf('File MIME type "%s" is not allowed', $mimeType)
            );
        }

        return ['valid' => true];
    }

    /**
     * Validate that the MIME type matches the file extension.
     * This prevents files like malicious.exe renamed to safe.pdf.
     */
    protected function validateMimeTypeMatchesExtension(UploadedFile $file): array
    {
        $extension = Str::lower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        // Define expected MIME types for each extension
        $extensionMimeMap = [
            'pdf' => ['application/pdf'],
            'doc' => ['application/msword'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'txt' => ['text/plain'],
            'rtf' => ['application/rtf', 'text/rtf'],
            'odt' => ['application/vnd.oasis.opendocument.text'],
            'xls' => ['application/vnd.ms-excel'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
            'csv' => ['text/csv', 'application/csv'],
            'ods' => ['application/vnd.oasis.opendocument.spreadsheet'],
            'ppt' => ['application/vnd.ms-powerpoint'],
            'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            'odp' => ['application/vnd.oasis.opendocument.presentation'],
            'jpg' => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'gif' => ['image/gif'],
            'webp' => ['image/webp'],
            'bmp' => ['image/bmp'],
            'svg' => ['image/svg+xml', 'image/svg'],
            'mp3' => ['audio/mpeg', 'audio/mp3'],
            'wav' => ['audio/wav', 'audio/wave'],
            'ogg' => ['audio/ogg'],
            'm4a' => ['audio/mp4', 'audio/x-m4a'],
            'aac' => ['audio/aac'],
            'mp4' => ['video/mp4'],
            'mov' => ['video/quicktime'],
            'avi' => ['video/x-msvideo', 'video/avi'],
            'mkv' => ['video/x-matroska'],
            'webm' => ['video/webm'],
        ];

        if (!isset($extensionMimeMap[$extension])) {
            // If we don't have a mapping, allow it (better false negative than false positive)
            return ['valid' => true];
        }

        $expectedMimeTypes = $extensionMimeMap[$extension];

        if (!in_array($mimeType, $expectedMimeTypes, true)) {
            return $this->rejected(
                $file,
                'mimetype_mismatch',
                sprintf(
                    'File MIME type "%s" does not match extension ".%s" (expected: %s)',
                    $mimeType,
                    $extension,
                    implode(', ', $expectedMimeTypes)
                )
            );
        }

        return ['valid' => true];
    }

    /**
     * Check for double extensions (e.g., file.php.jpg).
     */
    protected function validateNoDoubleExtensions(UploadedFile $file): array
    {
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();

        // Remove the last extension and check if there's another one
        $basename = Str::substr($filename, 0, - (strlen($extension) + 1));

        if (Str::contains($basename, '.') && preg_match('/\.\w+$/', $basename)) {
            return $this->rejected(
                $file,
                'double_extension',
                'Files with double extensions are not allowed for security reasons'
            );
        }

        return ['valid' => true];
    }

    /**
     * Sanitize a filename by removing dangerous characters.
     */
    public function sanitizeFilename(string $filename): string
    {
        // Get file extension
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);

        // Sanitize basename using Str::slug()
        $sanitizedBasename = Str::slug($basename, '-');

        // Remove any remaining problematic characters
        $sanitizedBasename = preg_replace('/[^a-zA-Z0-9\-_]/', '', $sanitizedBasename);

        // Remove consecutive hyphens and underscores
        $sanitizedBasename = preg_replace('/[-_]{2,}/', '-', $sanitizedBasename);

        // Trim hyphens and underscores from start/end
        $sanitizedBasename = trim($sanitizedBasename, '-_');

        // Ensure filename is not empty after sanitization
        if (empty($sanitizedBasename)) {
            $sanitizedBasename = 'unnamed_file';
        }

        // Reattach extension
        return $sanitizedBasename . '.' . $extension;
    }

    /**
     * Generate a secure random filename while preserving extension.
     */
    public function generateSecureFilename(UploadedFile $file): string
    {
        if (!Config::get('upload.security.generate_random_filenames', true)) {
            return $this->sanitizeFilename($file->getClientOriginalName());
        }

        $extension = $file->getClientOriginalExtension();

        return Str::random(40) . '.' . $extension;
    }

    /**
     * Log a rejected upload attempt.
     */
    protected function rejected(UploadedFile $file, string $reason, string $message): array
    {
        if (Config::get('upload.security.log_rejected_uploads', true)) {
            Log::warning('File upload rejected', [
                'reason' => $reason,
                'filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'extension' => $file->getClientOriginalExtension(),
                'ip' => request()?->ip(),
                'user_id' => auth()?->id(),
                'message' => $message,
            ]);
        }

        return [
            'valid' => false,
            'error' => $message,
            'reason' => $reason,
        ];
    }
}
