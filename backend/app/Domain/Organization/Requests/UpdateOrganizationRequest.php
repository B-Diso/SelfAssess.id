<?php

namespace App\Domain\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by OrganizationPolicy
    }

    public function rules(): array
    {
        $organizationId = $this->route('id');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('organizations')->ignore($organizationId),
            ],
            'description' => 'sometimes|nullable|string',
            'isActive' => 'sometimes|boolean',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('isActive')) {
            $this->merge(['is_active' => $this->isActive]);
        }
    }
}
