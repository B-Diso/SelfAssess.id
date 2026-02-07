<?php

namespace App\Domain\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganizationUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $routeUser = $this->route('user');
        $userId = $routeUser instanceof \App\Domain\User\Models\User ? $routeUser->id : $routeUser;

        return [
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('users')->ignore($userId),
            ],
            'password' => 'sometimes|string|min:8',
        ];
    }
}
