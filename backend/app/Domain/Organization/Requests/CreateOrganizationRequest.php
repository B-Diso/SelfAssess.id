<?php

namespace App\Domain\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by OrganizationPolicy
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:organizations,name',
            'description' => 'nullable|string',
        ];
    }
}
