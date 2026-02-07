<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Requests;

use App\Services\FileUploadValidator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class UploadAssessmentAttachmentRequest extends FormRequest
{
    protected FileUploadValidator $fileValidator;

    public function __construct(FileUploadValidator $fileValidator)
    {
        $this->fileValidator = $fileValidator;
    }

    /**
     * Determine if the user is authorized to make this request.
     * Note: Authorization is handled in the controller using policy checks.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $maxFileSizeKB = (int) (Config::get('upload.max_file_size', 2 * 1024 * 1024) / 1024);

        return [
            'assessment_response_id' => 'required|uuid|exists:assessment_responses,id',
            'file' => [
                'required',
                'file',
                "max:{$maxFileSizeKB}",
            ],
            'description' => 'nullable|string',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * Performs additional security validation on the uploaded file:
     * - Validates file extension against allowed list
     * - Validates MIME type from actual file content
     * - Checks for extension/MIME type mismatches
     * - Prevents double extension attacks
     * - Sanitizes filename
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $file = $this->file('file');

            if (!$file) {
                return;
            }

            // Perform comprehensive security validation
            $result = $this->fileValidator->validate($file);

            if (!$result['valid']) {
                $validator->errors()->add('file', $result['error']);
            }
        });
    }

    /**
     * Get the sanitized filename.
     */
    public function getSanitizedFilename(): string
    {
        $file = $this->file('file');

        if (!$file) {
            return 'unnamed_file';
        }

        return $this->fileValidator->sanitizeFilename($file->getClientOriginalName());
    }

    /**
     * Get a secure random filename for storage.
     */
    public function getSecureFilename(): string
    {
        $file = $this->file('file');

        if (!$file) {
            return Str::random(40);
        }

        return $this->fileValidator->generateSecureFilename($file);
    }
}
