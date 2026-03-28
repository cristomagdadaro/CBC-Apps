<?php

use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

Route::prefix('guest')->group(function () {
    Route::prefix('locations')->group(function () {
        Route::get('/regions', [LocationController::class, 'regions'])->name('api.locations.regions');
        Route::get('/provinces', [LocationController::class, 'provinces'])->name('api.locations.provinces');
        Route::get('/cities', [LocationController::class, 'cities'])->name('api.locations.cities');
    });
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::middleware(['role.any:admin,laboratory_manager,ict_manager,administrative_assistant'])->group(function () {
        Route::middleware(['api', 'auth'])->prefix('locations')->group(function () {
            Route::get('/regions', [LocationController::class, 'regions'])
                ->name('api.locations.regions.auth');

            Route::get('/provinces', [LocationController::class, 'provinces'])
                ->name('api.locations.provinces.auth');

            Route::get('/cities', [LocationController::class, 'cities'])
                ->name('api.locations.cities.auth');
        });
    });
});