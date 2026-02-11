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

// Authenticated routes organized by module
Route::middleware(['api', 'auth'])->group(function () {
    require __DIR__.'/laboratory.php';
    require __DIR__.'/locations.php';
    require __DIR__.'/forms.php';
    require __DIR__.'/inventory.php';
    require __DIR__.'/rental.php';
    require __DIR__.'/options.php';
});
