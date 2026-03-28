<?php

use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('laboratory')->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_EQUIPMENT_LOGGER])
        ->get('/equipments/{equipment_id?}', function ($equipment_id = null) {
            return Inertia::render('Laboratory/EquipmentShow', [
                'equipment_id' => $equipment_id,
                'logger_type' => 'laboratory',
            ]);
        })->name('laboratory.equipments.show');
});

Route::prefix('ict')->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_EQUIPMENT_LOGGER])
        ->get('/equipments/{equipment_id?}', function ($equipment_id = null) {
            return Inertia::render('Laboratory/EquipmentShow', [
                'equipment_id' => $equipment_id,
                'logger_type' => 'ict',
            ]);
        })->name('ict.equipments.show');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('apps')->group(function () {
        Route::prefix('laboratory')->group(function () {
            Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_LABORATORY_DASHBOARD])
                ->get('/', function () {
                    return Inertia::render('Laboratory/LaboratoryDashboard');
                })->name('laboratory.dashboard');
        });
    });
});