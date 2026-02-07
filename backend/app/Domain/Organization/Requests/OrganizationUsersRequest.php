<?php

namespace App\Domain\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrganizationUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by OrganizationPolicy via controller authorize
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'sortBy' => ['sometimes', 'string', \Illuminate\Validation\Rule::in(['name','email','created_at','updated_at'])],
            'sortOrder' => ['sometimes', 'string', \Illuminate\Validation\Rule::in(['asc','desc'])],
        ];
    }
}
