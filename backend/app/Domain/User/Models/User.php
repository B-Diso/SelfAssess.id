<?php

namespace App\Domain\User\Models;

use App\Domain\Organization\Models\Organization;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, HasUuids, SoftDeletes, HasRoles, Auditable;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'password',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The guard name for Spatie Permission
     */
    protected $guard_name = 'api';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Generate a new UUID v7 for the model.
     */
    public function newUniqueId(): string
    {
        return (string) \Illuminate\Support\Str::uuid7();
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     */
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     */
    public function getJWTCustomClaims(): array
    {
        return [
            'organization_id' => $this->organization_id,
            'email' => $this->email,
            'roles' => $this->getRoleNames(),
        ];
    }

    /**
     * Get the organization that owns the user.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Check if user is super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    /**
     * Check if user is organization admin.
     */
    public function isOrganizationAdmin(): bool
    {
        return $this->hasRole('organization_admin');
    }

    /**
     * Check if user can access organization.
     */
    public function canAccessOrganization(string $organizationId): bool
    {
        return $this->isSuperAdmin() || $this->organization_id === $organizationId;
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * Scope a query to only include users in organization.
     */
    public function scopeInOrganization($query, string $organizationId)
    {
        return $query->where('organization_id', $organizationId);
    }
}
