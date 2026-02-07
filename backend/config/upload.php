<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Maximum File Size
    |--------------------------------------------------------------------------
    |
    | The maximum file size in bytes. Default is 2MB for demo purposes.
    | This can be overridden via the UPLOAD_MAX_FILE_SIZE environment variable.
    | Format in .env: UPLOAD_MAX_FILE_SIZE=2097152 (for 2MB)
    |
    | Examples:
    | - 2MB   = 2 * 1024 * 1024 = 2097152
    | - 5MB   = 5 * 1024 * 1024 = 5242880
    | - 10MB  = 10 * 1024 * 1024 = 10485760
    | - 50MB  = 50 * 1024 * 1024 = 52428800
    |
    */

    'max_file_size' => (int) env('UPLOAD_MAX_FILE_SIZE', 2 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Allowed MIME Types
    |--------------------------------------------------------------------------
    |
    | List of allowed MIME types for file uploads. This provides defense in depth
    | by validating both the file extension AND the actual MIME type from file content.
    |
    | Security Note: MIME types are determined from file content, not just extension.
    | This prevents files with malicious content from bypassing validation.
    |
    */

    'allowed_mime_types' => [
        // Documents
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'application/rtf',
        'application/vnd.oasis.opendocument.text',

        // Spreadsheets
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
        'application/vnd.oasis.opendocument.spreadsheet',

        // Presentations
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.oasis.opendocument.presentation',

        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/bmp',
        'image/svg+xml',
        'image/svg',

        // Audio
        'audio/mpeg',
        'audio/mp3',
        'audio/wav',
        'audio/ogg',
        'audio/mp4',
        'audio/x-m4a',
        'audio/aac',

        // Video
        'video/mp4',
        'video/quicktime',
        'video/x-msvideo',
        'video/x-matroska',
        'video/webm',
    ],

    /*
    |--------------------------------------------------------------------------
    | Allowed File Extensions
    |--------------------------------------------------------------------------
    |
    | List of allowed file extensions (lowercase, without dot).
    | This provides the first layer of validation before MIME type checking.
    |
    | Documents: pdf, doc, docx, txt, rtf, odt
    | Spreadsheets: xls, xlsx, csv, ods
    | Presentations: ppt, pptx, odp
    | Images: jpg, jpeg, png, gif, webp, bmp, svg
    | Audio: mp3, wav, ogg, m4a, aac
    | Video: mp4, mov, avi, mkv, webm
    |
    | BLOCKED (Security Critical):
    | - Archives: zip, 7z, rar, tar, gz, bz2, iso
    | - Executables: exe, msi, app, dmg, deb, rpm
    | - Scripts: js, vbs, ps1, sh, bat, cmd
    |
    */

    'allowed_extensions' => [
        // Documents
        'pdf',
        'doc',
        'docx',
        'txt',
        'rtf',
        'odt',

        // Spreadsheets
        'xls',
        'xlsx',
        'csv',
        'ods',

        // Presentations
        'ppt',
        'pptx',
        'odp',

        // Images
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'bmp',
        'svg',

        // Audio
        'mp3',
        'wav',
        'ogg',
        'm4a',
        'aac',

        // Video
        'mp4',
        'mov',
        'avi',
        'mkv',
        'webm',
    ],

    /*
    |--------------------------------------------------------------------------
    | Filename Sanitization
    |--------------------------------------------------------------------------
    |
    | Characters to remove from filenames during sanitization.
    | This prevents path traversal attacks and other security issues.
    |
    */

    'sanitize_characters' => [
        '..', // Path traversal
        '\\', // Windows path separator
        '/',  // Unix path separator
        ':',  // Windows drive separator
        '*',  // Wildcard
        '?',  // Wildcard
        '"',  // Special character
        '<',  // Special character
        '>',  // Special character
        '|',  // Pipe character
        "\0", // Null byte
        "\n", // Newline
        "\r", // Carriage return
        "\t", // Tab
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Additional security settings for file uploads.
    |
    */

    'security' => [
        // Check for double extensions (e.g., file.php.jpg)
        'check_double_extensions' => env('UPLOAD_CHECK_DOUBLE_EXTENSIONS', true),

        // Store files outside webroot (set to false if using cloud storage)
        'store_outside_webroot' => env('UPLOAD_STORE_OUTSIDE_WEBROOT', true),

        // Generate random filenames on storage (keep original for display only)
        'generate_random_filenames' => env('UPLOAD_GENERATE_RANDOM_FILENAMES', true),

        // Log rejected upload attempts
        'log_rejected_uploads' => env('UPLOAD_LOG_REJECTED', true),
    ],
];
