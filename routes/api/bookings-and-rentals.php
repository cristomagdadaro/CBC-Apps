<?php

use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\RentalVehicleController;
use App\Http\Controllers\RentalVenueController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;

Route::prefix('guest')->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_RENTALS])->prefix('rental')->group(function () {
        Route::prefix('vehicles')->group(function () {
            Route::get('/', [RentalVehicleController::class, 'publicIndex'])->name('api.guest.rental.vehicles.index');
            Route::get('/{id}', [RentalVehicleController::class, 'publicShow'])->name('api.guest.rental.vehicles.show');
            Route::post('/', [RentalVehicleController::class, 'store'])->name('api.guest.rental.vehicles.store');
            Route::get('/check-availability/{vehicleType}/{dateFrom}/{dateTo}', [RentalVehicleController::class, 'checkAvailability'])
                ->name('api.guest.rental.vehicles.check-availability');
        });

        Route::prefix('venues')->group(function () {
            Route::get('/', [RentalVenueController::class, 'publicIndex'])->name('api.guest.rental.venues.index');
            Route::get('/{id}', [RentalVenueController::class, 'publicShow'])->name('api.guest.rental.venues.show');
            Route::post('/', [RentalVenueController::class, 'store'])->name('api.guest.rental.venues.store');
            Route::get('/check-availability/{venueType}/{dateFrom}/{dateTo}', [RentalVenueController::class, 'checkAvailability'])
                ->name('api.guest.rental.venues.check-availability');
        });
    });
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_RENTALS])->prefix('rental')->group(function () {
        Route::middleware(['can:rental.vehicle.manage'])->prefix('vehicles')->group(function () {
            Route::get('/', [RentalVehicleController::class, 'index'])->name('api.rental.vehicles.index');
            Route::post('/', [RentalVehicleController::class, 'store'])->name('api.rental.vehicles.store');
            Route::get('/{id}', [RentalVehicleController::class, 'show'])->name('api.rental.vehicles.show');
            Route::put('/{id}', [RentalVehicleController::class, 'update'])->name('api.rental.vehicles.update');
            Route::put('/{id}/status', [RentalVehicleController::class, 'updateStatus'])
                ->middleware(['can:rental.request.approve'])
                ->name('api.rental.vehicles.update-status');
            Route::delete('/{id}', [RentalVehicleController::class, 'destroy'])->name('api.rental.vehicles.destroy');
            Route::get('/check-availability/{vehicleType}/{dateFrom}/{dateTo}', [RentalVehicleController::class, 'checkAvailability'])->name('api.rental.vehicles.check-availability');
            Route::get('/by-type/{vehicleType}', [RentalVehicleController::class, 'getByVehicleType'])->name('api.rental.vehicles.by-type');
        });

        Route::middleware(['can:rental.venue.manage'])->prefix('venues')->group(function () {
            Route::get('/', [RentalVenueController::class, 'index'])->name('api.rental.venues.index');
            Route::post('/', [RentalVenueController::class, 'store'])->name('api.rental.venues.store');
            Route::get('/{id}', [RentalVenueController::class, 'show'])->name('api.rental.venues.show');
            Route::put('/{id}', [RentalVenueController::class, 'update'])->name('api.rental.venues.update');
            Route::put('/{id}/status', [RentalVenueController::class, 'updateStatus'])
                ->middleware(['can:rental.request.approve'])
                ->name('api.rental.venues.update-status');
            Route::delete('/{id}', [RentalVenueController::class, 'destroy'])->name('api.rental.venues.destroy');
            Route::get('/check-availability/{venueType}/{dateFrom}/{dateTo}', [RentalVenueController::class, 'checkAvailability'])->name('api.rental.venues.check-availability');
            Route::get('/by-type/{venueType}', [RentalVenueController::class, 'getByVenueType'])->name('api.rental.venues.by-type');
        });
    });

    Route::middleware(['role.any:admin,administrative_assistant'])->prefix('google-calendar')->group(function () {
        Route::get('/', [GoogleCalendarController::class, 'index'])->name('api.google-calendar.index');
        Route::post('/sync', [GoogleCalendarController::class, 'sync'])->name('api.google-calendar.sync');
        Route::post('/sync-batch', [GoogleCalendarController::class, 'syncBatch'])->name('api.google-calendar.sync-batch');
        Route::post('/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('api.google-calendar.disconnect');
    });
});