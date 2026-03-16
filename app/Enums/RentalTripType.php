<?php

namespace App\Enums;

enum RentalTripType: string
{
    case DEDICATED_TRIP = 'dedicated_trip';
    case DROP_OFF_AND_PICKUP = 'drop_off_and_pickup';
    case ONE_WAY_DROP_OFF = 'one_way_drop_off';
    case ONE_WAY_PICK_UP = 'one_way_pick_up';
    case SHUTTLE_SERVICE = 'shuttle_service';
    case MULTI_STOP_TRIP = 'multi_stop_trip';

    public static function values(): array
    {
        return array_map(
            static fn (self $type) => $type->value,
            self::cases(),
        );
    }
}
