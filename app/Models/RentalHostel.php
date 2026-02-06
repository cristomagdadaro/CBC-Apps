<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalHostel extends Model
{
    use SoftDeletes;

    protected $table = 'rental_hostels';

    protected $fillable = [
        'hostel_unit',
        'check_in_date',
        'check_out_date',
        'number_of_guests',
        'guest_name',
        'contact_number',
        'status',
        'notes',
    ];

    protected $dates = ['check_in_date', 'check_out_date', 'deleted_at'];

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
