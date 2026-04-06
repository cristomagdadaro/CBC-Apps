<?php

namespace App\Models\Research;

use App\Models\BaseModel;
use App\Models\User;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResearchProject extends BaseModel
{
    use HasFactory;
    use SoftDeletes;
    use Auditable;

    protected $fillable = [
        'code',
        'title',
        'commodity',
        'duration_start',
        'duration_end',
        'overall_budget',
        'objective',
        'funding_agency',
        'funding_code',
        'project_leader',
        'metadata',
        'created_by',
        'last_updated_by',
    ];

    protected $casts = [
        'duration_start' => 'date',
        'duration_end' => 'date',
        'overall_budget' => 'decimal:2',
        'objective' => 'encrypted',
        'project_leader' => 'encrypted:array',
        'metadata' => 'array',
    ];

    protected array $searchable = [
        'code',
        'title',
        'commodity',
        'funding_agency',
        'funding_code',
    ];

    protected $appends = [
        'route_identifier',
    ];

    public function studies(): HasMany
    {
        return $this->hasMany(ResearchStudy::class, 'project_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'research_project_user', 'project_id', 'user_id')
            ->withTimestamps();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by', 'id');
    }

    public function getRouteIdentifierAttribute(): string
    {
        return (string) ($this->funding_code ?: $this->code ?: $this->getKey());
    }

    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if ($field) {
            return parent::resolveRouteBinding($value, $field);
        }

        return static::query()
            ->where('funding_code', $value)
            ->orWhere('code', $value)
            ->orWhere($this->getQualifiedKeyName(), $value)
            ->first();
    }
}
