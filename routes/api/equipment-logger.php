<?php

use App\Http\Controllers\ICTEquipmentController;
use App\Http\Controllers\LaboratoryEquipmentController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;

Route::prefix('guest')->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_EQUIPMENT_LOGGER])->group(function () {
        Route::get('/lab/equipments/active/{employee_id?}', [LaboratoryEquipmentController::class, 'activeEquipments'])->name('api.laboratory.equipments.active');
        Route::get('/lab/equipments/{identifier}', [LaboratoryEquipmentController::class, 'show'])->name('api.laboratory.equipments.show');
        Route::get('/lab/equipments', [LaboratoryEquipmentController::class, 'index'])->name('api.laboratory.equipments.index');
        Route::post('/lab/equipments/{identifier}/check-in', [LaboratoryEquipmentController::class, 'checkIn'])->name('api.laboratory.equipments.check-in');
        Route::post('/lab/equipments/{identifier}/check-out', [LaboratoryEquipmentController::class, 'checkOut'])->name('api.laboratory.equipments.check-out');
        Route::post('/lab/equipments/{identifier}/update-end-use', [LaboratoryEquipmentController::class, 'updateEndUse'])->name('api.laboratory.equipments.update-end-use');
        Route::post('/lab/equipments/{identifier}/report-location', [LaboratoryEquipmentController::class, 'reportLocation'])->name('api.laboratory.equipments.report-location');

        Route::get('/ict/equipments/active/{employee_id?}', [ICTEquipmentController::class, 'activeEquipments'])->name('api.ict.equipments.active');
        Route::get('/ict/equipments/{identifier}', [ICTEquipmentController::class, 'show'])->name('api.ict.equipments.show');
        Route::get('/ict/equipments', [ICTEquipmentController::class, 'index'])->name('api.ict.equipments.index');
        Route::post('/ict/equipments/{identifier}/check-in', [ICTEquipmentController::class, 'checkIn'])->name('api.ict.equipments.check-in');
        Route::post('/ict/equipments/{identifier}/check-out', [ICTEquipmentController::class, 'checkOut'])->name('api.ict.equipments.check-out');
        Route::post('/ict/equipments/{identifier}/update-end-use', [ICTEquipmentController::class, 'updateEndUse'])->name('api.ict.equipments.update-end-use');
        Route::post('/ict/equipments/{identifier}/report-location', [ICTEquipmentController::class, 'reportLocation'])->name('api.ict.equipments.report-location');
    });
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::middleware(['can:laboratory.logger.manage', 'deployment.access:' . DeploymentAccessService::MODULE_LABORATORY_DASHBOARD])->prefix('equipment-logger')->group(function () {
        Route::get('/dashboard', [LaboratoryEquipmentController::class, 'dashboard'])->name('api.equipment-logger.dashboard');
        Route::get('/equipments', [LaboratoryEquipmentController::class, 'equipmentIndex'])->name('api.equipment-logger.equipments.index');
        Route::get('/personnels', [LaboratoryEquipmentController::class, 'personnelIndex'])->name('api.equipment-logger.personnels.index');
        Route::get('/personnels/{personnelId}', [LaboratoryEquipmentController::class, 'personnelSummary'])->name('api.equipment-logger.personnels.show');
        Route::get('/personnels/{personnelId}/logs', [LaboratoryEquipmentController::class, 'personnelLogs'])->name('api.equipment-logger.personnels.logs.index');
        Route::patch('/equipments/{equipmentId}/logger-mode', [LaboratoryEquipmentController::class, 'updateEquipmentLoggerMode'])->name('api.equipment-logger.equipments.logger-mode.update');
    });

    Route::middleware(['can:laboratory.logger.manage', 'deployment.access:' . DeploymentAccessService::MODULE_LABORATORY_DASHBOARD])->prefix('laboratory')->group(function () {
        Route::get('/dashboard', [LaboratoryEquipmentController::class, 'dashboard'])->name('api.laboratory.dashboard');
        Route::get('/logs', [LaboratoryEquipmentController::class, 'logs'])->name('api.laboratory.logs.index');
    });
});
