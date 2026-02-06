<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalVehicle extends Model
{
    use SoftDeletes;

    protected $table = 'rental_vehicles';

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

    protected $dates = ['date_from', 'date_to', 'deleted_at'];

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

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
}
