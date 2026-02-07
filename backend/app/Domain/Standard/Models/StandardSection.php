<?php

declare(strict_types=1);

namespace App\Domain\Standard\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class StandardSection extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'standard_id',
        'parent_id',
        'type',
        'code',
        'title',
        'description',
        'level',
    ];

    protected $casts = [
        'level' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($section) {
            if (empty($section->level)) {
                if ($section->parent_id) {
                    $parent = static::find($section->parent_id);
                    $section->level = $parent ? $parent->level + 1 : 0;
                } else {
                    $section->level = 0;
                }
            }
        });
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(StandardSection::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(StandardSection::class, 'parent_id')->orderBy('level');
    }

    public function requirements(): HasMany
    {
        return $this->hasMany(StandardRequirement::class);
    }
}
