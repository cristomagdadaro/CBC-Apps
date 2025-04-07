<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\LabRequestController;
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


Route::prefix('guest')->group(function () {
    Route::prefix('forms')->group(function () {
        Route::get('/{event_id?}', [FormController::class, 'index'])->name('api.form.guest.index');
        Route::middleware(['check.form.suspended','check.form.expired','check.form.maxslot'])->post('/registration/{event_id?}', [ParticipantController::class, 'post'])->name('api.form.registration.post');
    });

});


Route::middleware(['api','auth:sanctum','verified'])->group(function () {
    Route::prefix('forms')->group(function () {
        Route::prefix('event')->group(function () {
            Route::get('/', [FormController::class, 'index'])->name('api.form.index');
            Route::get('/{event_id?}', [FormController::class, 'show'])->name('api.form.show');
            Route::post('/create', [FormController::class, 'create'])->name('api.form.post');
            Route::delete('/delete/{event_id?}', [FormController::class, 'delete'])->name('api.form.delete');
            Route::middleware(['check.form.suspended'])->put('/update/{event_id?}', [FormController::class, 'update'])->name('api.form.put');
        });

        Route::prefix('lab-request')->group(function () {
            Route::post('/create', [LabRequestController::class, 'create'])->name('api.labReq.post');
        });
    });
});
