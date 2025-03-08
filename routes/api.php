<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\RegistrationController;
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


Route::middleware('guest')->prefix('guest')->group(function () {
    Route::prefix('forms')->group(function () {
        Route::get('/{event_id?}', [FormController::class, 'index'])->name('api.form.guest.index');
        Route::post('/registration/{event_id?}', [ParticipantController::class, 'post'])->name('api.form.registration.post');
    });

});


Route::middleware(['api','auth:sanctum','verified'])->group(function () {
    Route::prefix('forms')->group(function () {
        Route::get('/', [FormController::class, 'index'])->name('api.form.index');
        Route::post('/create', [FormController::class, 'post'])->name('api.form.post');
    });
});
