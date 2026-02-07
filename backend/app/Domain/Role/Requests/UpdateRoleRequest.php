<?php

namespace App\Domain\Role\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-roles') ?? false;
    }

    public function rules(): array
    {
        // Get the role from route model binding (parameter name is 'role', not 'id')
        $role = $this->route('role');
        $id = $role ? $role->id : null;

        return [
            'name' => 'sometimes|string|max:255|unique:roles,name,' . $id,
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }
}
