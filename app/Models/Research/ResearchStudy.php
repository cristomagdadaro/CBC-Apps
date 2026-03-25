<?php

namespace App\Models\Research;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchStudy extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    protected $fillable = [
        'project_id',
        'code',
        'title',
        'objective',
        'budget',
        'study_leader',
        'staff_members',
        'supervisor',
        'metadata',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'objective' => 'encrypted',
        'budget' => 'decimal:2',
        'study_leader' => 'encrypted:array',
        'staff_members' => 'encrypted:array',
        'supervisor' => 'encrypted:array',
        'metadata' => 'array',
    ];

    protected array $searchable = [
        'code',
        'title',
        'project.title',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ResearchProject::class, 'project_id');
    }

    public function experiments(): HasMany
    {
        return $this->hasMany(ResearchExperiment::class, 'study_id');
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
