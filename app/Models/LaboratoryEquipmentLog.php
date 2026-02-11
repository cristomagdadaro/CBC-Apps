<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use DateTimeInterface;
use Illuminate\Support\Str;

class LaboratoryEquipmentLog extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }
    
    protected $fillable = [
        'equipment_id',
        'personnel_id',
        'status',
        'started_at',
        'end_use_at',
        'actual_end_at',
        'active_lock',
        'purpose',
        'checked_in_by',
        'checked_out_by',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'end_use_at' => 'datetime',
        'actual_end_at' => 'datetime',
        'active_lock' => 'boolean',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'equipment_id', 'id');
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class, 'personnel_id', 'id');
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by', 'id');
    }

    public function checkedOutBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_out_by', 'id');
    }
}
