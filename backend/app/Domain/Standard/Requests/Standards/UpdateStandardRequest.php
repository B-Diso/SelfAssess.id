<?php

declare(strict_types=1);

namespace App\Domain\Standard\Requests\Standards;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStandardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'version' => 'sometimes|required|string|max:50',
            'type' => 'nullable|string|in:internal,regulatory,standard,bestPractice,other',
            'description' => 'nullable|string',
            'isActive' => 'boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('isActive')) {
            $this->merge(['is_active' => $this->isActive]);
        }
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (isset($validated['isActive'])) {
            $validated['is_active'] = $validated['isActive'];
            unset($validated['isActive']);
        }

        return $validated;
    }
}
