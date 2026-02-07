<?php

namespace App\Domain\Role\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Enforce permission; route middleware also guards this
        return $this->user()?->can('manage-roles') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:roles,name',
            'guardName' => 'sometimes|string|default:api',
            'permissions' => 'sometimes|array',
            'permissions.*' => 'exists:permissions,name',
        ];
    }
}
