<?php

namespace App\Domain\Auth\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'organizationId' => $this->organization_id,
            'organizationName' => $this->organization->name,
            'isMasterOrganization' => $this->organization->isMaster(),
            'organizationDescription' => $this->organization->description,
            'organizationIsActive' => $this->organization->is_active,
            'name' => $this->name,
            'email' => $this->email,
            'emailVerifiedAt' => $this->email_verified_at?->toIso8601String(),
            'roles' => $this->roles->pluck('name'),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'lastLoginAt' => $this->last_login_at?->toIso8601String(),
            'createdAt' => $this->created_at->toIso8601String(),
            'updatedAt' => $this->updated_at->toIso8601String(),
        ];
    }
}
