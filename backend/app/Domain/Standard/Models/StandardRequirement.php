<?php

declare(strict_types=1);

namespace App\Domain\Standard\Models;

use App\Domain\Assessment\Models\AssessmentResponse;
use App\Domain\Attachment\Models\Attachment;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StandardRequirement extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'standard_section_id',
        'display_code',
        'title',
        'description',
        'evidence_hint',
    ];

    /**
     * Requirement belongs to a Section
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(StandardSection::class, 'standard_section_id');
    }

    /**
     * Requirement has many AssessmentResponses
     */
    public function assessmentResponses(): HasMany
    {
        return $this->hasMany(AssessmentResponse::class, 'standard_requirement_id');
    }

    /**
     * Requirement linked to multiple attachments (many-to-many via bridge table)
     */
    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(
            Attachment::class,
            'standard_attachments',
            'standard_requirement_id',
            'attachment_id'
        )->withTimestamps();
    }
}
