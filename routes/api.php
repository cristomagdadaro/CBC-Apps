<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Import API routes grouped by owning module.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

require __DIR__.'/api/shared.php';
require __DIR__.'/api/form-builder.php';
require __DIR__.'/api/bookings-and-rentals.php';
require __DIR__.'/api/fes-request.php';
require __DIR__.'/api/equipment-logger.php';
require __DIR__.'/api/research.php';
require __DIR__.'/api/inventory.php';
require __DIR__.'/api/file-reports.php';
require __DIR__.'/api/options.php';
require __DIR__.'/api/user-management.php';
