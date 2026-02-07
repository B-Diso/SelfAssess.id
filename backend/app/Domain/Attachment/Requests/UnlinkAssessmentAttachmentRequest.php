<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnlinkAssessmentAttachmentRequest extends FormRequest
{
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
     * Note: Route parameters (assessment_response, attachment) are validated by route model binding.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [];
    }
}
