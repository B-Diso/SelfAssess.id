<?php

namespace App\Domain\Organization\Models;

use App\Domain\User\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organization extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected static function newFactory()
    {
        return \Database\Factories\OrganizationFactory::new();
    }

    protected $fillable = ['name', 'description', 'is_active', 'parent_id'];

    protected $casts = ['is_active' => 'boolean'];

    public function parent()
    {
        return $this->belongsTo(Organization::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Organization::class, 'parent_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function activeUsers()
    {
        return $this->users()->active();
    }

    public function admins()
    {
        return $this->users()->whereHas('roles', function ($q) {
            $q->where('name', 'organization_admin');
        });
    }

    public function isMaster(): bool
    {
        return $this->name === config('organization.master.name');
    }

    public function hasActiveMembers(): bool
    {
        return $this->activeUsers()->exists();
    }

    public function canBeDeleted(): bool
    {
        return !$this->isMaster() && !$this->hasActiveMembers();
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($organization) {
            if ($organization->isMaster()) {
                throw new \Exception('Cannot delete ' . config('organization.master.name'));
            }

            if ($organization->hasActiveMembers()) {
                throw new \Exception('Cannot delete organization with active members');
            }
        });
    }
}
