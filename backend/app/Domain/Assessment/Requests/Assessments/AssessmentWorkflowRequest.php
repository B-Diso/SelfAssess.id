<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Requests\Assessments;

use App\Domain\Assessment\Enums\AssessmentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Request for transitioning assessment workflow status.
 *
 * Maps to AssessmentWorkflowResponse which includes:
 * - assessment: The updated Assessment resource
 * - transition: The WorkflowLog with transition details
 */
class AssessmentWorkflowRequest extends FormRequest
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
            'status' => ['required', 'string', Rule::in(AssessmentStatus::values())],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
