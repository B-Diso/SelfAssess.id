<?php

namespace App\Domain\User\Requests;

use App\Domain\Organization\Models\Organization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSuperAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'organizationId' => [
                'required',
                'uuid',
                Rule::exists('organizations', 'id')->whereNull('deleted_at'),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('organizationId')) {
            $this->merge(['organization_id' => $this->organizationId]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $role = $this->input('role');
            $organizationId = $this->input('organization_id');

            if ($role === 'super_admin' && $organizationId) {
                $organization = Organization::find($organizationId);

                if (! $organization || ! $organization->isMaster()) {
                    $validator->errors()->add('organizationId', 'Super Admin can only belong to ' . config('organization.master.name'));
                }
            }
        });
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (isset($validated['organizationId'])) {
            $validated['organization_id'] = $validated['organizationId'];
            unset($validated['organizationId']);
        }

        return $validated;
    }
}
