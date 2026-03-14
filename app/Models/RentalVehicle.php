<?php

namespace App\Models;

use Database\Factories\RentalVehicleFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use DateTimeInterface;

class RentalVehicle extends Model
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

    protected $table = 'rental_vehicles';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'vehicle_type',
        'date_from',
        'date_to',
        'time_from',
        'time_to',
        'purpose',
        'requested_by',
        'contact_number',
        'status',
        'notes',
    ];

    protected $dates = ['date_from', 'date_to', 'deleted_at', 'updated_at', 'created_at'];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    protected static function newFactory()
    {
        return RentalVehicleFactory::new();
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('g:i a M j, Y');
    }
}
