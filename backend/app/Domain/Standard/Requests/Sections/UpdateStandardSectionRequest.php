<?php

declare(strict_types=1);

namespace App\Domain\Standard\Requests\Sections;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStandardSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isSuperAdmin();
    }

    public function rules(): array
    {
        return [
            'parentId' => 'nullable|exists:standard_sections,id',
            'type' => 'nullable|string|max:50',
            'code' => 'sometimes|required|string|max:50',
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'sometimes|integer|min:0',
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('parentId')) {
            $this->merge(['parent_id' => $this->parentId]);
        }
    }

    public function validated($key = null, $default = null)
    {
        $validated = parent::validated($key, $default);

        if (isset($validated['parentId'])) {
            $validated['parent_id'] = $validated['parentId'];
            unset($validated['parentId']);
        }

        return $validated;
    }
}
