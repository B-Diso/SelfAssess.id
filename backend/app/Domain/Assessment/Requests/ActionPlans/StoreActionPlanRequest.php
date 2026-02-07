<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Requests\ActionPlans;

use Illuminate\Foundation\Http\FormRequest;

class StoreActionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assessmentId' => 'required|exists:assessments,id',
            'assessmentResponseId' => 'required|exists:assessment_responses,id',
            'title' => 'required|string|max:255',
            'actionPlan' => 'nullable|string',
            'dueDate' => 'nullable|date',
            'pic' => 'nullable|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('assessmentId')) {
            $this->merge(['assessment_id' => $this->assessmentId]);
        }
        if ($this->has('assessmentResponseId')) {
            $this->merge(['assessment_response_id' => $this->assessmentResponseId]);
        }
        if ($this->has('actionPlan')) {
            $this->merge(['action_plan' => $this->actionPlan]);
        }
        if ($this->has('dueDate')) {
            $this->merge(['due_date' => $this->dueDate]);
        }
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (isset($validated['assessmentId'])) {
            $validated['assessment_id'] = $validated['assessmentId'];
            unset($validated['assessmentId']);
        }
        if (isset($validated['assessmentResponseId'])) {
            $validated['assessment_response_id'] = $validated['assessmentResponseId'];
            unset($validated['assessmentResponseId']);
        }
        if (isset($validated['actionPlan'])) {
            $validated['action_plan'] = $validated['actionPlan'];
            unset($validated['actionPlan']);
        }
        if (isset($validated['dueDate'])) {
            $validated['due_date'] = $validated['dueDate'];
            unset($validated['dueDate']);
        }

        return $validated;
    }
}
