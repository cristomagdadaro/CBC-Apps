<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class AuditLog extends BaseModel
{
    use HasUuids;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'model_type',
        'model_id',
        'action',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'description',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'id' => 'string',
    ];

    /**
     * Get the user who made the change.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the audited model.
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by model type.
     */
    public function scopeForModel($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope to filter by action.
     */
    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by user.
     */
    public function scopeByUser($query, string|int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get a human-readable description of what changed.
     */
    public function getChangeSummary(): string
    {
        if ($this->action === 'created') {
            return "Created by " . ($this->user?->name ?? 'Unknown');
        }

        if ($this->action === 'deleted') {
            return "Deleted by " . ($this->user?->name ?? 'Unknown');
        }

        if ($this->action === 'force_deleted') {
            return "Permanently deleted by " . ($this->user?->name ?? 'Unknown');
        }

        $changes = [];
        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                if (isset($this->old_values[$key]) && $this->old_values[$key] !== $newValue) {
                    $changes[] = "$key: {$this->old_values[$key]} → $newValue";
                }
            }
        }

        return count($changes) > 0
            ? "Updated " . implode(", ", array_slice($changes, 0, 3)) . (count($changes) > 3 ? ", +more" : "")
            : "Updated by " . ($this->user?->name ?? 'Unknown');
    }
}
