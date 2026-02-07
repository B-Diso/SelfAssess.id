<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Requests\ActionPlans;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActionPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actionPlan = $this->route('assessment_action_plan');
        $response = $actionPlan->assessmentResponse ?? $actionPlan->assessmentResponse()->withTrashed()->first();

        if (! $response) {
            return false;
        }

        return $this->user()->can('manageActionPlan', $response);
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'actionPlan' => 'nullable|string',
            'dueDate' => 'nullable|date',
            'pic' => 'nullable|string|max:255',
        ];
    }

    protected function prepareForValidation(): void
    {
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
