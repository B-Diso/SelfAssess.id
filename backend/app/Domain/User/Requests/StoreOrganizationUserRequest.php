<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganizationUserRequest extends FormRequest
{
    protected bool $organizationIdProvided = false;

    protected bool $organizationIdSnakeProvided = false;

    public function authorize(): bool
    {
        // Authorization handled via UserPolicy in controller
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'organizationId' => ['prohibited'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->organizationIdProvided = $this->has('organizationId');
        $this->organizationIdSnakeProvided = $this->has('organization_id');
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->organizationIdProvided) {
                $validator->errors()->add('organizationId', 'organizationId is not allowed for this endpoint.');
            }

            if ($this->organizationIdSnakeProvided) {
                $validator->errors()->add('organizationId', 'organizationId is not allowed for this endpoint.');
            }
        });
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        unset($validated['organizationId']);

        return $validated;
    }
}
