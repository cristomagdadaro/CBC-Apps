<?php

use App\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Locations Routes
|--------------------------------------------------------------------------
|
| Routes for location/address data (regions, provinces, cities)
|
*/

Route::middleware(['api', 'auth'])->prefix('locations')->group(function () {
    Route::get('/regions', [LocationController::class, 'regions'])
        ->name('api.locations.regions.auth');
    
    Route::get('/provinces', [LocationController::class, 'provinces'])
        ->name('api.locations.provinces.auth');
    
    Route::get('/cities', [LocationController::class, 'cities'])
        ->name('api.locations.cities.auth');
});
