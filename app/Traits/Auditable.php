<?php

namespace App\Traits;

use App\Observers\AuditObserver;

trait Auditable
{
    /**
     * Boot the Auditable trait.
     * This will automatically register the AuditObserver when a model uses this trait.
     */
    public static function bootAuditable(): void
    {
        static::observe(AuditObserver::class);
    }

    /**
     * Get all audit logs for this model instance.
     */
    public function auditLogs()
    {
        return $this->hasMany(\App\Models\AuditLog::class, 'model_id')
            ->where('model_type', static::class)
            ->latest('created_at');
    }

    /**
     * Get the complete change history for this model.
     */
    public function getAuditHistory()
    {
        return $this->auditLogs()->get();
    }

    /**
     * Get changes made in a specific audit log entry.
     */
    public function getAuditChanges($auditLogId)
    {
        return $this->auditLogs()
            ->where('id', $auditLogId)
            ->first();
    }

    /**
     * Get who created this record.
     */
    public function getCreatedByUser()
    {
        return $this->auditLogs()
            ->where('action', 'created')
            ->first()
            ?->user;
    }

    /**
     * Get who last modified this record.
     */
    public function getLastModifiedByUser()
    {
        return $this->auditLogs()
            ->where('action', 'updated')
            ->first()
            ?->user;
    }

    /**
     * Check if this record has been modified since creation.
     */
    public function hasBeenModified(): bool
    {
        return $this->auditLogs()
            ->where('action', 'updated')
            ->exists();
    }
}
