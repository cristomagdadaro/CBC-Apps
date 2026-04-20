<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;
use Illuminate\Support\Str;

class Item extends BaseModel
{
    use HasFactory, SoftDeletes, HasUuids, Auditable;

    public const EQUIPMENT_LOGGER_MODE_EXCLUDED = 'excluded';
    public const EQUIPMENT_LOGGER_MODE_TRACKED_ONLY = 'tracked_only';
    public const EQUIPMENT_LOGGER_MODE_BORROWABLE = 'borrowable';

    public const EQUIPMENT_LOGGER_MODES = [
        self::EQUIPMENT_LOGGER_MODE_EXCLUDED,
        self::EQUIPMENT_LOGGER_MODE_TRACKED_ONLY,
        self::EQUIPMENT_LOGGER_MODE_BORROWABLE,
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $table = 'items';

    protected $casts = [
        'id' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $fillable = [
        'id',
        'name',
        'brand',
        'description',
        'specifications',
        'category_id',
        'supplier_id',
        'equipment_logger_mode',
        'image',
    ];

    protected array $searchable  = [
        'id',
        'name',
        'brand',
        'description',
        'specifications',
        'category_id',
        'supplier_id',
        'equipment_logger_mode',
        'image',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('g:i a M j, Y');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'item_id', 'id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(SuppEquipReport::class, 'item_id', 'id');
    }

    public static function resolveEquipmentLoggerMode(?string $requestedMode, mixed $categoryId): string
    {
        $normalizedCategoryId = (int) ($categoryId ?? 0);

        if (! in_array($normalizedCategoryId, [4, 7], true)) {
            return self::EQUIPMENT_LOGGER_MODE_EXCLUDED;
        }

        if (in_array($requestedMode, self::EQUIPMENT_LOGGER_MODES, true)) {
            return $requestedMode;
        }

        return self::EQUIPMENT_LOGGER_MODE_TRACKED_ONLY;
    }
}
