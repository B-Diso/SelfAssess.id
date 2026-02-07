<?php

namespace App\Domain\User\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthenticatedUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'organizationId' => (string) $this->organization_id,
            'organizationName' => $this->whenLoaded('organization', fn () => $this->organization?->name),
            'name' => $this->name,
            'email' => $this->email,
            'roles' => $this->roles->pluck('name')->values(),
            'permissions' => $this->getAllPermissions()->pluck('name')->values(),
            'lastLoginAt' => optional($this->last_login_at)?->toIso8601String(),
            'createdAt' => optional($this->created_at)?->toIso8601String(),
            'updatedAt' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
