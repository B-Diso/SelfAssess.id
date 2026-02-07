<?php

declare(strict_types=1);

namespace App\Domain\Attachment\Models;

use App\Domain\Organization\Models\Organization;
use App\Domain\Standard\Models\StandardRequirement;
use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\User\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attachment extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'organization_id',
        'name',
        'mime_type',
        'size',
        'path',
        'disk',
        'category',
        'description',
        'created_by_id',
        'updated_by_id',
    ];

    /**
     * Get the organization that owns the attachment (Document Center).
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Standard requirements that use this attachment as reference material.
     */
    public function standardRequirements(): BelongsToMany
    {
        return $this->belongsToMany(StandardRequirement::class, 'standard_attachments')
            ->withPivot(['id', 'standard_id'])
            ->withTimestamps();
    }

    /**
     * Assessment responses that use this attachment as evidence.
     */
    public function assessmentResponses(): BelongsToMany
    {
        return $this->belongsToMany(
            AssessmentResponse::class,
            'assessment_attachments',
            'attachment_id',           // Foreign key on pivot table for this model
            'assessment_response_id'   // Foreign key on pivot table for related model
        )
            ->withPivot(['id', 'assessment_id'])
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }
}
