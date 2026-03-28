<?php

use App\Http\Controllers\LaboratoryEquipmentController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Laboratory Routes
|--------------------------------------------------------------------------
|
| Routes for laboratory equipment management and dashboard
|
*/

Route::middleware(['can:laboratory.logger.manage', 'deployment.access:' . DeploymentAccessService::MODULE_LABORATORY_DASHBOARD])->prefix('laboratory')->group(function () {
    Route::get('/dashboard', [LaboratoryEquipmentController::class, 'dashboard'])->name('api.laboratory.dashboard');
    Route::get('/logs', [LaboratoryEquipmentController::class, 'logs'])->name('api.laboratory.logs.index');
});
