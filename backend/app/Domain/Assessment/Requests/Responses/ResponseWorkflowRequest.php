<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Requests\Responses;

use App\Domain\Assessment\Enums\AssessmentResponseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request for transitioning assessment response workflow status.
 *
 * ⚠️ IMPORTANT: Uses AssessmentResponseStatus, NOT AssessmentStatus!
 * Response flow: active → pending_review → reviewed
 *
 * Maps to ResponseWorkflowResponse which includes:
 * - response: The updated AssessmentResponse resource
 * - transition: The WorkflowLog with transition details
 */
class ResponseWorkflowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization is handled by the controller policy check.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(AssessmentResponseStatus::values())],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
