<?php

namespace App\Domain\Organization\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IndexOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization handled by OrganizationPolicy (viewAny)
    }

    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'sortBy' => ['sometimes', 'string', Rule::in(['name', 'created_at', 'updated_at'])],
            'sortOrder' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
