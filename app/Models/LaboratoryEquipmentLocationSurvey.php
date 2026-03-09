<?php

namespace App\Models;

use App\Traits\Auditable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class LaboratoryEquipmentLocationSurvey extends BaseModel
{
    use HasFactory, HasUuids, Auditable;

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
        'id',
        'equipment_id',
        'personnel_id',
        'location_code',
        'location_label',
        'reported_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    protected array $searchable = [
        'equipment_id',
        'personnel_id',
        'location_code',
        'location_label',
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
}
