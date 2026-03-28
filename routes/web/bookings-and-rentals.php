<?php

use App\Http\Controllers\GoogleCalendarController;
use App\Repositories\OptionRepo;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_RENTALS])->prefix('rental')->group(function () {
    Route::get('/vehicle', function () {
        return Inertia::render('Rentals/VehicleRentalFormGuest', [
            'vehicleOptions' => app(OptionRepo::class)->getVehicles(),
        ]);
    })->name('rental.vehicle.guest');

    Route::get('/venue', function () {
        return Inertia::render('Rentals/VenueRentalFormGuest', [
            'venueOptions' => app(OptionRepo::class)->getEventHalls(),
        ]);
    })->name('rental.venue.guest');

    Route::get('/bookings', function () {
        return Inertia::render('Rentals/BookingRentalsPublic');
    })->name('rental.bookings.guest');

    Route::get('/vehicle/{id}', function (string $id) {
        return Inertia::render('Rentals/RentalVehicleShow', [
            'rental_id' => $id,
        ]);
    })->name('rental.vehicle.show');

    Route::get('/venue/{id}', function (string $id) {
        return Inertia::render('Rentals/RentalVenueShow', [
            'rental_id' => $id,
        ]);
    })->name('rental.venue.show');

    Route::get('google-calendar', function () {
        return Inertia::render('Rentals/GoogleCalendarPublic');
    })->name('google-calendar.rentals');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('apps')->group(function () {
        Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_RENTALS])->prefix('rentals')->group(function () {
            Route::get('/vehicle', function () {
                return Inertia::render('Rentals/RentalsVehicleIndex', [
                    'vehicleOptions' => app(OptionRepo::class)->getVehicles(),
                ]);
            })->name('rentals.vehicle.index');

            Route::get('/venue', function () {
                return Inertia::render('Rentals/RentalsVenueIndex');
            })->name('rentals.venue.index');

            Route::middleware(['role.any:admin,administrative_assistant'])->get('/calendar', function () {
                return Inertia::render('Rentals/RentalsGoogleCalendar');
            })->name('rentals.calendar.index');

            Route::middleware(['role.any:admin,administrative_assistant'])->get('/calendar/google/connect', [GoogleCalendarController::class, 'redirectToOauth'])
                ->name('rentals.calendar.oauth.redirect');

            Route::middleware(['role.any:admin,administrative_assistant'])->get('/calendar/google/callback', [GoogleCalendarController::class, 'handleOauthCallback'])
                ->name('rentals.calendar.oauth.callback');
        });
    });
});