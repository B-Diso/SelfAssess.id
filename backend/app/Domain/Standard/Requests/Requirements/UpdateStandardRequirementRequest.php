<?php

declare(strict_types=1);

namespace App\Domain\Standard\Requests\Requirements;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStandardRequirementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'displayCode' => 'sometimes|required|string|max:50',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'evidenceHint' => 'nullable|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('displayCode')) {
            $this->merge(['display_code' => $this->displayCode]);
        }
        if ($this->has('evidenceHint')) {
            $this->merge(['evidence_hint' => $this->evidenceHint]);
        }
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (isset($validated['displayCode'])) {
            $validated['display_code'] = $validated['displayCode'];
            unset($validated['displayCode']);
        }
        if (isset($validated['evidenceHint'])) {
            $validated['evidence_hint'] = $validated['evidenceHint'];
            unset($validated['evidenceHint']);
        }

        return $validated;
    }
}
