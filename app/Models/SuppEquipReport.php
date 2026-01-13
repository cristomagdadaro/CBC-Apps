<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuppEquipReport extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'supp_equip_reports';

    protected $fillable = [
        'id',
        'transaction_id',
        'item_id',
        'user_id',
        'report_type',
        'report_data',
        'notes',
        'reported_at',
    ];

    protected array $searchable = [
        'report_type',
        'notes',
    ];

    protected $casts = [
        'id' => 'string',
        'transaction_id' => 'string',
        'item_id' => 'string',
        'user_id' => 'integer',
        'report_data' => 'array',
        'reported_at' => 'datetime',
    ];

    protected $dates = [
        'reported_at',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
