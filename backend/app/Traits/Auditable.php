<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            static::logAudit('created', $model, null, $model->getAttributes());
        });

        static::updated(function ($model) {
            static::logAudit('updated', $model, $model->getOriginal(), $model->getChanges());
        });

        static::deleted(function ($model) {
            static::logAudit('deleted', $model, $model->getAttributes(), null);
        });
    }

    protected static function logAudit(string $action, $model, ?array $oldValues, ?array $newValues)
    {
        $user = Auth::user();

        $logData = [
            'timestamp' => now()->toIso8601String(),
            'action' => $action,
            'model' => get_class($model),
            'model_id' => $model->getKey(),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'organization_id' => $user?->organization_id ?? $model->organization_id ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $oldValues ? static::sanitizeLogData($oldValues) : null,
            'new_values' => $newValues ? static::sanitizeLogData($newValues) : null,
        ];

        Log::channel('audit')->info('Model ' . $action, $logData);
    }

    protected static function sanitizeLogData(array $data): array
    {
        return collect($data)
            ->except(['password', 'remember_token'])
            ->toArray();
    }
}
