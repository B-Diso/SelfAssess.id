<?php

declare(strict_types=1);

namespace App\Domain\Standard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Standard extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'version',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function sections(): HasMany
    {
        return $this->hasMany(StandardSection::class)->whereNull('parent_id')->orderBy('level');
    }

    /**
     * Get all sections regardless of nesting level
     */
    public function allSections(): HasMany
    {
        return $this->hasMany(StandardSection::class);
    }
}
