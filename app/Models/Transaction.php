<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends BaseModel
{
    use HasFactory, SoftDeletes, HasUuids;

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
        'project_code',
        'personnel_id',
        'user_id',
        'expiration',
        'remarks',
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
        'project_code',
        'personnel_id',
        'user_id',
        'expiration',
        'remarks',
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
}
