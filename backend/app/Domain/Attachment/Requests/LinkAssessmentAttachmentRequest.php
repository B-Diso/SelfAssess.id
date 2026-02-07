<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LinkAssessmentAttachmentRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'assessment_response_id' => 'required|uuid|exists:assessment_responses,id',
            'attachment_id' => 'required|uuid|exists:attachments,id',
        ];
    }
}
