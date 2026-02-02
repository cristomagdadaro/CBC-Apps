<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Support\Str;

class Transaction extends BaseModel
{
    use HasFactory, SoftDeletes, HasUuids, Auditable;

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $table = 'transactions';
    protected $keyType = 'string';
    protected $casts = [
        'id' => 'string',
    ];
    protected $fillable = [
        'id',
        'item_id',
        'barcode',
        'transac_type',
        'quantity',
        'unit_price',
        'unit',
        'total_cost',
        'personnel_id',
        'user_id',
        'expiration',
        'remarks',
        'project_code',
    ];

    protected array $searchable = [
        'id',
        'item_id',
        'barcode',
        'transac_type',
        'quantity',
        'unit_price',
        'unit',
        'total_cost',
        'personnel_id',
        'user_id',
        'expiration',
        'remarks',
        'project_code',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('g:i a M j, Y');
    }

    public function item()
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function user()
    {
        return$this->belongsTo(User::class, 'user_id', 'id');
    }

    public function personnel()
    {
        return$this->belongsTo(Personnel::class, 'personnel_id', 'id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(SuppEquipReport::class, 'transaction_id', 'id');
    }
}
