<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('staff.dashboard', function ($user) {
    return $user !== null;
});

Broadcast::channel('inventory.transactions', function ($user) {
    return $user?->can('inventory.manage') ?? false;
});

Broadcast::channel('inventory.checkout', function ($user) {
    return $user?->can('inventory.manage') ?? false;
});

Broadcast::channel('inventory.items', function ($user) {
    return $user?->can('inventory.manage') ?? false;
});

Broadcast::channel('inventory.suppliers', function ($user) {
    return $user?->can('inventory.manage') ?? false;
});

Broadcast::channel('inventory.personnels', function ($user) {
    return $user?->can('inventory.manage') ?? false;
});

Broadcast::channel('laboratory.logs', function ($user) {
    return $user?->can('laboratory.logger.manage') ?? false;
});

Broadcast::channel('ict.logs', function ($user) {
    return $user?->can('laboratory.logger.manage') ?? false;
});

Broadcast::channel('equipment.user.{employeeId}', function ($user, $employeeId) {
    if ($user?->can('laboratory.logger.manage')) {
        return true;
    }

    return trim((string) $user?->employee_id) === trim((string) $employeeId);
});

Broadcast::channel('rentals.calendar', function ($user) {
    return ($user?->can('rental.vehicle.manage') ?? false)
        || ($user?->can('rental.venue.manage') ?? false)
        || ($user?->can('rental.request.approve') ?? false);
});

Broadcast::channel('rentals.vehicles', function ($user) {
    return ($user?->can('rental.vehicle.manage') ?? false)
        || ($user?->can('rental.request.approve') ?? false);
});

Broadcast::channel('rentals.venues', function ($user) {
    return ($user?->can('rental.venue.manage') ?? false)
        || ($user?->can('rental.request.approve') ?? false);
});

Broadcast::channel('forms.event.{eventId}', function ($user, $eventId) {
    return ($user?->can('event.forms.manage') ?? false)
        || ($user?->can('event.certificates.manage') ?? false);
});

Broadcast::channel('certificates.batch.{batchId}', function ($user, $batchId) {
    return $user?->can('event.certificates.manage') ?? false;
});

Broadcast::channel('research.samples', function ($user) {
    return ($user?->can('research.samples.manage') ?? false)
        || ($user?->can('research.monitoring.manage') ?? false);
});
