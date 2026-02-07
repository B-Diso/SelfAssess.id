<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Models;

use App\Domain\Organization\Models\Organization;
use App\Domain\Standard\Models\Standard;
use App\Domain\Assessment\Enums\AssessmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected static function newFactory()
    {
        return \Database\Factories\Domain\Assessment\Models\AssessmentFactory::new();
    }

    protected $fillable = [
        'organization_id',
        'standard_id',
        'name',
        'period_value',
        'start_date',
        'end_date',
        'status',
    ];

    /**
     * Application-layer validation for assessment status.
     * Validates that status is one of the allowed enum values.
     * This ensures data integrity while using VARCHAR in database (not ENUM).
     *
     * Note: We use getRawOriginal() to check the attribute value BEFORE casting.
     * This handles both string values (from direct assignment) and enum values.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->isDirty('status')) {
                // Get the raw status value before casting
                $statusValue = $model->getAttributes()['status'] ?? null;

                // Convert enum to string if needed
                if ($statusValue instanceof AssessmentStatus) {
                    $statusValue = $statusValue->value;
                }

                if (!in_array($statusValue, AssessmentStatus::values())) {
                    throw new \InvalidArgumentException(
                        "Invalid assessment status: {$statusValue}. " .
                        "Must be one of: " . implode(', ', AssessmentStatus::values())
                    );
                }
            }
        });
    }

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'status' => AssessmentStatus::class,
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function standard(): BelongsTo
    {
        return $this->belongsTo(Standard::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(AssessmentResponse::class);
    }

    public function workflowLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AssessmentWorkflowLog::class, 'loggable');
    }
}
