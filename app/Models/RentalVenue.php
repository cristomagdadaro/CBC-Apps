<?php

namespace App\Models;

use Database\Factories\RentalVenueFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentalVenue extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'rental_venues';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'venue_type',
        'date_from',
        'date_to',
        'time_from',
        'time_to',
        'expected_attendees',
        'event_name',
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

    protected static function newFactory()
    {
        return RentalVenueFactory::new();
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
}