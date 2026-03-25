<?php

namespace App\Models\Research;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchMonitoringRecord extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    protected $fillable = [
        'sample_id',
        'stage',
        'recorded_on',
        'parameter_set',
        'notes',
        'selected_for_export',
        'recorded_by',
    ];

    protected $casts = [
        'recorded_on' => 'date',
        'parameter_set' => 'encrypted:array',
        'notes' => 'encrypted',
        'selected_for_export' => 'boolean',
    ];

    protected array $searchable = [
        'stage',
        'sample.uid',
        'sample.accession_name',
    ];

    public function sample(): BelongsTo
    {
        return $this->belongsTo(ResearchSample::class, 'sample_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by', 'id');
    }
}
