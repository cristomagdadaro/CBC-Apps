<?php

namespace App\Models\Research;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchSample extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    protected $fillable = [
        'experiment_id',
        'uid',
        'sequence_number',
        'commodity',
        'sample_type',
        'accession_name',
        'pr_code',
        'field_label',
        'line_label',
        'plant_label',
        'generation',
        'plot_number',
        'field_number',
        'replication_number',
        'current_status',
        'current_location',
        'storage_location',
        'germination_date',
        'sowing_date',
        'harvest_date',
        'is_priority',
        'legacy_reference',
        'metadata',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'germination_date' => 'date',
        'sowing_date' => 'date',
        'harvest_date' => 'date',
        'is_priority' => 'boolean',
        'metadata' => 'array',
    ];

    protected array $searchable = [
        'uid',
        'accession_name',
        'pr_code',
        'field_label',
        'line_label',
        'plant_label',
        'current_location',
        'storage_location',
        'experiment.title',
    ];

    public function experiment(): BelongsTo
    {
        return $this->belongsTo(ResearchExperiment::class, 'experiment_id');
    }

    public function monitoringRecords(): HasMany
    {
        return $this->hasMany(ResearchMonitoringRecord::class, 'sample_id')
            ->orderByDesc('recorded_on')
            ->orderByDesc('id');
    }

    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(ResearchSampleInventoryLog::class, 'sample_id')
            ->latest('created_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by', 'id');
    }
}
