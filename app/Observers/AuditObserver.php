<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->createAuditLog($model, 'created', null, $model->getAttributes());
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $old = $model->getOriginal();
        $new = $model->getChanges();

        // Only log if there are actual changes (excluding timestamps if unchanged)
        if (!empty($new)) {
            $this->createAuditLog($model, 'updated', $old, $new);
        }
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        // Soft deletes - store the complete model data
        $this->createAuditLog($model, 'deleted', $model->getOriginal(), null);
    }

    /**
     * Handle the Model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        // Hard deletes - store the complete model data
        $this->createAuditLog($model, 'force_deleted', $model->getOriginal(), null);
    }

    /**
     * Create an audit log entry.
     */
    protected function createAuditLog(
        Model $model,
        string $action,
        ?array $oldValues,
        ?array $newValues
    ): void {
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'action' => $action,
                'old_values' => $this->filterSensitiveData($oldValues),
                'new_values' => $this->filterSensitiveData($newValues),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'description' => null,
            ]);
        } catch (\Exception $e) {
            // Log audit failure but don't interrupt the main operation
            \Illuminate\Support\Facades\Log::warning('Failed to create audit log: ' . $e->getMessage());
        }
    }

    /**
     * Filter out sensitive data from values.
     */
    protected function filterSensitiveData(?array $values): ?array
    {
        if (!$values) {
            return $values;
        }

        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'secret',
            'api_key',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($values[$field])) {
                $values[$field] = '[REDACTED]';
            }
        }

        return $values;
    }
}
