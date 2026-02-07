<?php

namespace App\Domain\User\Requests;

use App\Domain\Organization\Models\Organization;
use App\Domain\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSuperAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeUser = $this->route('user');
        $userId = $routeUser instanceof User ? $routeUser->id : $routeUser;

        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($userId),
            ],
            'password' => 'sometimes|string|min:8',
            'role' => ['sometimes', 'string', Rule::exists('roles', 'name')],
            'organizationId' => [
                'sometimes',
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
            /** @var User|null $target */
            $target = $this->route('user');
            $organizationId = $this->input('organization_id');
            $role = $this->input('role');

            if ($target instanceof User && $organizationId && $organizationId !== (string) $target->organization_id) {
                $validator->errors()->add('organizationId', 'Use transfer endpoint to change organization.');
            }

            if ($target instanceof User && $role === 'super_admin') {
                $organization = $target->organization()->first();
                if (! $organization || ! $organization->isMaster()) {
                    $validator->errors()->add('role', 'Super Admin can only belong to ' . config('organization.master.name'));
                }
            }

            if ($role === 'super_admin' && $organizationId) {
                $organization = Organization::find($organizationId);
                if ($organization && ! $organization->isMaster()) {
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
