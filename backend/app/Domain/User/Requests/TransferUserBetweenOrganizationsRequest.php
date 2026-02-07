<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferUserBetweenOrganizationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'userId' => 'required|uuid|exists:users,id',
            'targetOrganizationId' => [
                'required',
                'uuid',
                Rule::exists('organizations', 'id')->whereNull('deleted_at'),
            ],
            'reason' => 'nullable|string|max:500',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->userId,
            'target_organization_id' => $this->targetOrganizationId,
        ]);
    }
}
