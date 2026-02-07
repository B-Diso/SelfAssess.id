<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentActionPlan extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected static function newFactory()
    {
        return \Database\Factories\Domain\Assessment\Models\AssessmentActionPlanFactory::new();
    }

    protected $table = 'assessment_action_plans';

    protected $fillable = [
        'assessment_id',
        'assessment_response_id',
        'title',
        'action_plan',
        'due_date',
        'pic',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function assessmentResponse(): BelongsTo
    {
        return $this->belongsTo(AssessmentResponse::class, 'assessment_response_id');
    }
}
