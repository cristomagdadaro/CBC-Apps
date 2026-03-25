<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

/*
|--------------------------------------------------------------------------
| Module Routes
|--------------------------------------------------------------------------
|
| Import modular route files for better organization
|
*/

// Public/Guest routes (no authentication required)
require __DIR__.'/guest.php';
require __DIR__.'/options.php';

// Authenticated routes organized by module
Route::middleware(['api', 'auth:sanctum'])->group(function () {
    require __DIR__.'/laboratory.php';
    Route::middleware(['role.any:admin,administrative_assistant'])->group(function () {
        require __DIR__.'/calendar.php';
    });

    Route::middleware(['role.any:admin,laboratory_manager,ict_manager,administrative_assistant'])->group(function () {
        require __DIR__.'/locations.php';
    });

    require __DIR__.'/forms.php';

    Route::middleware(['can:fes.request.approve', 'role.any:admin'])->group(function () {
        require __DIR__.'/fes.php';
    });

    require __DIR__.'/inventory.php';

    require __DIR__.'/rental.php';

    require __DIR__.'/research.php';

    require __DIR__.'/users.php';
});
