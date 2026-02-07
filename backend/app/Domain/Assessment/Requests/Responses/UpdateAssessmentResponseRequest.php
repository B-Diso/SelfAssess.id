<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Requests\Responses;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentResponseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'status' => 'sometimes|string|in:' . implode(',', \App\Domain\Assessment\Enums\AssessmentResponseStatus::values()),
            'comments' => 'nullable|string',
            'compliance_status' => 'sometimes|string|in:non_compliant,partially_compliant,fully_compliant,not_applicable',
        ];
    }
}
