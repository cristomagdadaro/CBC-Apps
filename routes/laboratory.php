<?php

use App\Http\Controllers\LaboratoryEquipmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Laboratory Routes
|--------------------------------------------------------------------------
|
| Routes for laboratory equipment management and dashboard
|
*/

Route::middleware(['can:laboratory.logger.manage'])->prefix('laboratory')->group(function () {
    Route::get('/dashboard', [LaboratoryEquipmentController::class, 'dashboard'])->name('api.laboratory.dashboard');
    Route::get('/logs', [LaboratoryEquipmentController::class, 'logs'])->name('api.laboratory.logs.index');
});
