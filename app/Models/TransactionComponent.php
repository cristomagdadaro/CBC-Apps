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
        'component_transaction_id',
    ];

    protected array $searchable = [
        'transaction_id',
        'component_transaction_id',
    ];

    protected $casts = [
        'id' => 'string',
        'transaction_id' => 'string',
        'component_transaction_id' => 'string',
    ];

    public function parentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'id');
    }

    public function componentTransaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'component_transaction_id', 'id');
    }
}
