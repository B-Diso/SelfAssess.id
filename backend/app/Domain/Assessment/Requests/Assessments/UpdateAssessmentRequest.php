<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Requests\Assessments;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('assessment')) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'periodValue' => 'nullable|string|max:255',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
            'status' => 'sometimes|required|string|in:' . implode(',', \App\Domain\Assessment\Enums\AssessmentStatus::values()),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('periodValue')) {
            $this->merge(['period_value' => $this->periodValue]);
        }
        if ($this->has('startDate')) {
            $this->merge(['start_date' => $this->startDate]);
        }
        if ($this->has('endDate')) {
            $this->merge(['end_date' => $this->endDate]);
        }
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (isset($validated['periodValue'])) {
            $validated['period_value'] = $validated['periodValue'];
            unset($validated['periodValue']);
        }
        if (isset($validated['startDate'])) {
            $validated['start_date'] = $validated['startDate'];
            unset($validated['startDate']);
        }
        if (isset($validated['endDate'])) {
            $validated['end_date'] = $validated['endDate'];
            unset($validated['endDate']);
        }

        return $validated;
    }
}
