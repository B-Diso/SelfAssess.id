<?php

declare(strict_types=1);

namespace App\Domain\Assessment\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AssessmentWorkflowLog extends Model
{
    use HasUuids;

    protected $table = 'assessment_workflow_logs';

    protected $fillable = [
        'loggable_id',
        'loggable_type',
        'from_status',
        'to_status',
        'note',
        'user_id',
    ];

    public function loggable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
