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
        \Illuminate\Support\Facades\Log::info('AuditObserver: deleted event triggered for ' . get_class($model) . ' with ID: ' . $model->getKey());
        $this->createAuditLog($model, 'deleted', $model->getOriginal(), null);
    }

    /**
     * Handle the Model "force deleted" event.
     */
    public function forceDeleted(Model $model): void
    {
        // Hard deletes - store the complete model data
        \Illuminate\Support\Facades\Log::info('AuditObserver: forceDeleted event triggered for ' . get_class($model) . ' with ID: ' . $model->getKey());
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
            \Illuminate\Support\Facades\Log::info('AuditObserver: Creating audit log for ' . $action . ' action');
            
            $auditData = [
                'user_id' => Auth::id(),
                'model_type' => get_class($model),
                'model_id' => $model->getKey(),
                'action' => $action,
                'old_values' => $this->filterSensitiveData($oldValues),
                'new_values' => $this->filterSensitiveData($newValues),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'description' => null,
            ];
            
            \Illuminate\Support\Facades\Log::info('Audit data to insert: ' . json_encode($auditData));
            
            AuditLog::create($auditData);
            
            \Illuminate\Support\Facades\Log::info('Audit log created successfully');
        } catch (\Exception $e) {
            // Log audit failure but don't interrupt the main operation
            \Illuminate\Support\Facades\Log::warning('Failed to create audit log: ' . $e->getMessage(), ['exception' => $e]);
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
