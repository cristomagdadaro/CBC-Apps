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
        'created_at' => 'datetime:Y-m-d\TH:i:sP',
        'updated_at' => 'datetime:Y-m-d\TH:i:sP',
    ];

    protected $appends = [
        'actor_name',
        'change_summary',
    ];

    /**
     * Get the resolved actor name for this audit log.
     */
    public function getActorNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        if ($this->model_type === \App\Models\Transaction::class) {
            $personnelId = $this->new_values['personnel_id'] ?? $this->old_values['personnel_id'] ?? null;
            if ($personnelId) {
                $personnel = \App\Models\Personnel::find($personnelId);
                if ($personnel) {
                    return app(\App\Support\TransactionActorNameResolver::class)->resolve($personnel, null) ?? 'Unknown';
                }
            }
        }

        return 'Unknown';
    }

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
            return "Created";
        }

        if ($this->action === 'deleted') {
            return "Deleted";
        }

        if ($this->action === 'force_deleted') {
            return "Permanently deleted";
        }

        $changes = [];
        $ignoredFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'user_id', 'personnel_id', 'employee_id'];
        
        $fieldLabels = [
            'item_id' => 'Item ID',
            'project_code' => 'Project Code',
            'quantity' => 'Quantity',
            'remarks' => 'Remarks',
            'unit' => 'Unit',
            'barcode' => 'Barcode',
            'storage_location' => 'Storage Location',
            'condition' => 'Condition',
            'par_no' => 'PAR No',
            'barcode_prri' => 'PRRI Barcode',
            'status' => 'Status',
        ];

        if ($this->old_values && $this->new_values) {
            foreach ($this->new_values as $key => $newValue) {
                if (in_array($key, $ignoredFields)) {
                    continue;
                }
                
                // Use loose comparison for numbers/strings
                if (isset($this->old_values[$key]) && $this->old_values[$key] != $newValue) {
                    $label = $fieldLabels[$key] ?? ucfirst(str_replace('_', ' ', $key));
                    $oldVal = $this->old_values[$key];
                    
                    if (is_string($oldVal) && strlen($oldVal) > 30) $oldVal = substr($oldVal, 0, 27) . '...';
                    if (is_string($newValue) && strlen($newValue) > 30) $newValue = substr($newValue, 0, 27) . '...';
                    if (is_array($oldVal)) $oldVal = 'Array';
                    if (is_array($newValue)) $newValue = 'Array';
                    if (is_bool($oldVal)) $oldVal = $oldVal ? 'Yes' : 'No';
                    if (is_bool($newValue)) $newValue = $newValue ? 'Yes' : 'No';
                    if ($oldVal === null || $oldVal === '') $oldVal = 'None';
                    if ($newValue === null || $newValue === '') $newValue = 'None';

                    $changes[] = "$label: $oldVal → $newValue";
                }
            }
        }

        return count($changes) > 0
            ? "Updated " . implode(", ", array_slice($changes, 0, 3)) . (count($changes) > 3 ? ", +more" : "")
            : "Updated";
    }

    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d\TH:i:sP');
    }

    public function getChangeSummaryAttribute(): string
    {
        return $this->getChangeSummary();
    }
}
