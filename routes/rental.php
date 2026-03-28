<?php

use App\Http\Controllers\RentalVehicleController;
use App\Http\Controllers\RentalVenueController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;

Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_RENTALS])->prefix('rental')->group(function () {
    // Vehicle Rentals
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

    // Venue Rentals
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
