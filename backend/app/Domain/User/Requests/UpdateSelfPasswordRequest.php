<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSelfPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'currentPassword' => ['required', 'string'],
            'newPassword' => ['required', 'string', 'min:8', 'confirmed', 'different:currentPassword'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'current_password' => $this->input('currentPassword'),
            'new_password' => $this->input('newPassword'),
        ]);
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        return [
            'current_password' => $validated['currentPassword'],
            'new_password' => $validated['newPassword'],
        ];
    }
}
