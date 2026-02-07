/**
 * File upload configuration and validation
 *
 * SECURITY NOTE: Frontend validation is for UX only.
 * Backend must always validate file types and sizes.
 */

/**
 * Maximum file size in bytes (default: 2MB)
 * Can be overridden per upload scenario
 */
export const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB

/**
 * Allowed MIME types for file upload
 */
export const ALLOWED_MIME_TYPES = [
  // Documents
  "application/pdf",
  "application/msword",
  "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
  "text/plain",
  "application/rtf",
  "application/vnd.oasis.opendocument.text",

  // Spreadsheets
  "application/vnd.ms-excel",
  "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
  "text/csv",
  "application/vnd.oasis.opendocument.spreadsheet",

  // Presentations
  "application/vnd.ms-powerpoint",
  "application/vnd.openxmlformats-officedocument.presentationml.presentation",
  "application/vnd.oasis.opendocument.presentation",

  // Images
  "image/jpeg",
  "image/png",
  "image/gif",
  "image/webp",
  "image/bmp",
  "image/svg+xml",

  // Audio
  "audio/mpeg",
  "audio/wav",
  "audio/ogg",
  "audio/mp4",
  "audio/x-m4a",
  "audio/aac",

  // Video
  "video/mp4",
  "video/quicktime",
  "video/x-msvideo",
  "video/x-matroska",
  "video/webm",
] as const;

/**
 * Allowed file extensions
 */
export const ALLOWED_EXTENSIONS = [
  // Documents
  ".pdf",
  ".doc",
  ".docx",
  ".txt",
  ".rtf",
  ".odt",

  // Spreadsheets
  ".xls",
  ".xlsx",
  ".csv",
  ".ods",

  // Presentations
  ".ppt",
  ".pptx",
  ".odp",

  // Images
  ".jpg",
  ".jpeg",
  ".png",
  ".gif",
  ".webp",
  ".bmp",
  ".svg",

  // Audio
  ".mp3",
  ".wav",
  ".ogg",
  ".m4a",
  ".aac",

  // Video
  ".mp4",
  ".mov",
  ".avi",
  ".mkv",
  ".webm",
] as const;

/**
 * Blocked file extensions (dangerous types)
 */
export const BLOCKED_EXTENSIONS = [
  // Archives
  ".zip",
  ".7z",
  ".rar",
  ".tar",
  ".gz",
  ".bz2",
  ".iso",

  // Executables
  ".exe",
  ".msi",
  ".app",
  ".dmg",
  ".deb",
  ".rpm",

  // Scripts
  ".js",
  ".vbs",
  ".ps1",
  ".sh",
  ".bat",
  ".cmd",
] as const;

/**
 * File type categories with their extensions for UI display
 */
export const FILE_TYPE_CATEGORIES = {
  documents: {
    label: "Dokumen",
    extensions: ["PDF", "DOC", "DOCX", "TXT", "RTF", "ODT"],
  },
  spreadsheets: {
    label: "Spreadsheet",
    extensions: ["XLS", "XLSX", "CSV", "ODS"],
  },
  presentations: {
    label: "Presentasi",
    extensions: ["PPT", "PPTX", "ODP"],
  },
  images: {
    label: "Gambar",
    extensions: ["JPG", "PNG", "GIF", "WEBP", "BMP", "SVG"],
  },
  audio: {
    label: "Audio",
    extensions: ["MP3", "WAV", "OGG", "M4A", "AAC"],
  },
  video: {
    label: "Video",
    extensions: ["MP4", "MOV", "AVI", "MKV", "WEBM"],
  },
} as const;

/**
 * Validation result type
 */
export interface FileValidationResult {
  valid: boolean;
  error?: string;
}

/**
 * Validate a file for upload
 *
 * @param file - The file to validate
 * @param maxSize - Maximum file size in bytes (defaults to MAX_FILE_SIZE)
 * @returns Validation result with error message if invalid
 */
export function validateFile(
  file: File,
  maxSize: number = MAX_FILE_SIZE,
): FileValidationResult {
  // Check file size
  if (file.size > maxSize) {
    const maxSizeMB = (maxSize / (1024 * 1024)).toFixed(0);
    return {
      valid: false,
      error: `Ukuran file terlalu besar. Maksimum ${maxSizeMB}MB.`,
    };
  }

  // Check if file extension is blocked
  const fileName = file.name.toLowerCase();
  const hasBlockedExtension = BLOCKED_EXTENSIONS.some((ext) =>
    fileName.endsWith(ext),
  );

  if (hasBlockedExtension) {
    return {
      valid: false,
      error: "Tipe file ini tidak diperbolehkan demi keamanan.",
    };
  }

  // Check file extension
  const hasAllowedExtension = ALLOWED_EXTENSIONS.some((ext) =>
    fileName.endsWith(ext),
  );

  if (!hasAllowedExtension) {
    return {
      valid: false,
      error:
        "Tipe file tidak didukung. Silakan upload dokumen, spreadsheet, presentasi, gambar, audio, atau video.",
    };
  }

  // Check MIME type (if available)
  // Some browsers may not provide MIME type for all files
  if (file.type && !ALLOWED_MIME_TYPES.includes(file.type as any)) {
    return {
      valid: false,
      error: "Tipe file tidak dikenali atau tidak didukung.",
    };
  }

  return { valid: true };
}

/**
 * Get file extension from filename
 */
export function getFileExtension(filename: string): string {
  const lastDotIndex = filename.lastIndexOf(".");
  if (lastDotIndex === -1) return "";
  return filename.slice(lastDotIndex).toLowerCase();
}

/**
 * Format file size for display
 */
export function formatFileSize(bytes: number): string {
  if (bytes < 1024) return bytes + " B";
  if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + " KB";
  return (bytes / (1024 * 1024)).toFixed(1) + " MB";
}

/**
 * Generate accept attribute value for file input
 */
export function getAcceptAttributeValue(): string {
  return [
    // Documents
    ".pdf,.doc,.docx,.txt,.rtf,.odt",
    // Spreadsheets
    ".xls,.xlsx,.csv,.ods",
    // Presentations
    ".ppt,.pptx,.odp",
    // Images
    ".jpg,.jpeg,.png,.gif,.webp,.bmp,.svg",
    // Audio
    ".mp3,.wav,.ogg,.m4a,.aac",
    // Video
    ".mp4,.mov,.avi,.mkv,.webm",
  ].join(",");
}
