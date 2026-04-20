<?php

namespace App\Models;

use App\Support\TransactionActorNameResolver;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Support\Str;

class Transaction extends BaseModel
{
    use HasFactory, SoftDeletes, HasUuids, Auditable;

    public const OPTION_KEY_EQUIPMENT_LOGGER_MODES = 'equipment_logger_modes';
    public const EQUIPMENT_LOGGER_MODE_EXCLUDED = 'excluded';
    public const EQUIPMENT_LOGGER_MODE_TRACKED_ONLY = 'tracked_only';
    public const EQUIPMENT_LOGGER_MODE_BORROWABLE = 'borrowable';

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
    protected $appends = [
        'actor_display_name',
    ];
    protected $fillable = [
        'id',
        'item_id',
        'barcode',
        'barcode_prri',
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
        'equipment_logger_mode',
        'par_no',
        'condition',
    ];

    protected array $searchable = [
        'id',
        'item_id',
        'item.name',
        'item.brand',
        'item.description',
        'barcode',
        'barcode_prri',
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
        'equipment_logger_mode',
        'par_no',
        'condition',
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

    public function getActorDisplayNameAttribute(): ?string
    {
        return app(TransactionActorNameResolver::class)->resolve(
            $this->personnel,
            $this->user,
        );
    }

    public function components(): HasMany
    {
        return $this->hasMany(TransactionComponent::class, 'transaction_id', 'id');
    }

    public function parentComponents(): HasMany
    {
        return $this->hasMany(TransactionComponent::class, 'component_transaction_id', 'id');
    }

    public function parentComponent(): HasOne
    {
        return $this->hasOne(TransactionComponent::class, 'component_transaction_id', 'id');
    }
}
