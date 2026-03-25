<?php

namespace App\Models\Research;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchExperiment extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    protected $fillable = [
        'study_id',
        'code',
        'title',
        'geographic_location',
        'season',
        'commodity',
        'sample_type',
        'sample_descriptor',
        'pr_code',
        'cross_combination',
        'parental_background',
        'filial_generation',
        'generation',
        'plot_number',
        'field_number',
        'replication_number',
        'planned_plant_count',
        'background_notes',
        'metadata',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'cross_combination' => 'encrypted',
        'parental_background' => 'encrypted',
        'background_notes' => 'encrypted',
        'metadata' => 'array',
    ];

    protected array $searchable = [
        'code',
        'title',
        'geographic_location',
        'season',
        'commodity',
        'sample_type',
        'pr_code',
        'study.title',
    ];

    public function study(): BelongsTo
    {
        return $this->belongsTo(ResearchStudy::class, 'study_id');
    }

    public function samples(): HasMany
    {
        return $this->hasMany(ResearchSample::class, 'experiment_id');
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
