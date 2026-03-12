<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class TransactionComponent extends BaseModel
{
    use HasFactory, HasUuids, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $table = 'transaction_components';

    protected $fillable = [
        'id',
        'transaction_id',
        'item_id',
        'quantity',
        'unit',
        'barcode_prri',
        'prri_component_no',
        'expiration',
        'remarks',
    ];

    protected array $searchable = [
        'transaction_id',
        'item_id',
        'quantity',
        'unit',
        'barcode_prri',
        'prri_component_no',
        'expiration',
        'remarks',
    ];

    protected $casts = [
        'id' => 'string',
        'transaction_id' => 'string',
        'item_id' => 'string',
        'quantity' => 'float',
        'barcode_prri' => 'string',
        'prri_component_no' => 'string',
        'expiration' => 'date',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_id', 'id');
    }
}
