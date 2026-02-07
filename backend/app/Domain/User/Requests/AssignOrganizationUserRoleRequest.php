<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignOrganizationUserRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name'),
            ],
        ];
    }
}
