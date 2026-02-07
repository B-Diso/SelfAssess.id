<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Models;

use App\Domain\Standard\Models\StandardRequirement;
use App\Domain\Attachment\Models\Attachment;
use App\Domain\Assessment\Models\AssessmentActionPlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Domain\Assessment\Enums\AssessmentResponseStatus;

class AssessmentResponse extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'assessment_id',
        'standard_requirement_id',
        'comments',
        'compliance_status',
        'status', // Explicitly add status to fillable
    ];

    protected $casts = [
        'status' => AssessmentResponseStatus::class,
    ];

    /**
     * Boot the model and add validation layer.
     *
     * Note: We use getAttributes() to check the raw attribute value BEFORE casting.
     * This handles both string values (from direct assignment) and enum values.
     */
    protected static function boot()
    {
        parent::boot();

        // Validate status on save to ensure data integrity
        static::saving(function ($model) {
            if ($model->isDirty('status')) {
                // Get the raw status value before casting
                $statusValue = $model->getAttributes()['status'] ?? null;

                // Convert enum to string if needed
                if ($statusValue instanceof AssessmentResponseStatus) {
                    $statusValue = $statusValue->value;
                }

                if (!in_array($statusValue, AssessmentResponseStatus::values())) {
                    throw new \InvalidArgumentException(
                        "Invalid assessment response status: {$statusValue}. " .
                        "Must be one of: " . implode(', ', AssessmentResponseStatus::values())
                    );
                }
            }
        });
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function requirement(): BelongsTo
    {
        return $this->belongsTo(StandardRequirement::class, 'standard_requirement_id');
    }

    /**
     * Assessment evidence linked from the Document Center (Many-to-Many).
     */
    public function attachments(): BelongsToMany
    {
        return $this->belongsToMany(
            Attachment::class,
            'assessment_attachments',
            'assessment_response_id', // Foreign key on pivot table for this model
            'attachment_id'           // Foreign key on pivot table for related model
        )
            ->withPivot(['id', 'assessment_id'])
            ->withTimestamps();
    }

    public function actionPlans(): HasMany
    {
        return $this->hasMany(AssessmentActionPlan::class, 'assessment_response_id');
    }

    public function workflowLogs(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(AssessmentWorkflowLog::class, 'loggable');
    }

    /**
     * Transition to a new status with validation.
     *
     * Valid Transitions:
     * - active → pending_review (User finished filling)
     * - pending_review → reviewed (Reviewer approves)
     * - pending_review → active (Reviewer returns/rejects)
     * - reviewed → active (Admin found error, needs correction)
     *
     * @throws \InvalidArgumentException
     */
    public function transitionTo(string $newStatus, ?string $note = null): bool
    {
        $allowedTransitions = [
            AssessmentResponseStatus::ACTIVE->value => [
                AssessmentResponseStatus::PENDING_REVIEW->value,
            ],
            AssessmentResponseStatus::PENDING_REVIEW->value => [
                AssessmentResponseStatus::ACTIVE->value, // reject
                AssessmentResponseStatus::REVIEWED->value,
            ],
            AssessmentResponseStatus::REVIEWED->value => [
                AssessmentResponseStatus::ACTIVE->value, // revert if error found
            ],
        ];

        $currentStatus = $this->getOriginal('status') ?? $this->status;
        $currentStatusValue = is_string($currentStatus) ? $currentStatus : $currentStatus->value;

        if (!isset($allowedTransitions[$currentStatusValue]) ||
            !in_array($newStatus, $allowedTransitions[$currentStatusValue])) {
            $allowed = isset($allowedTransitions[$currentStatusValue])
                ? implode(', ', $allowedTransitions[$currentStatusValue])
                : 'none';
            throw new \InvalidArgumentException(
                "Cannot transition from {$currentStatusValue} to {$newStatus}. Allowed: {$allowed}"
            );
        }

        $fromStatus = $currentStatusValue;
        $this->status = $newStatus;
        $this->save();

        // Log the transition
        $this->workflowLogs()->create([
            'from_status' => $fromStatus,
            'to_status' => $newStatus,
            'note' => $note,
            'user_id' => auth()->id(),
        ]);

        return true;
    }
}
