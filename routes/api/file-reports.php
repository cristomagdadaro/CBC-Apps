<?php

use App\Http\Controllers\SuppEquipReportController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_INVENTORY])->prefix('inventory')->group(function () {
        Route::middleware(['can:equipment.report.manage'])->prefix('supp-equip-reports')->group(function () {
            Route::get('/', [SuppEquipReportController::class, 'index'])->name('api.inventory.supp_equip_reports.index');
            Route::get('/templates', [SuppEquipReportController::class, 'templates'])->name('api.inventory.supp_equip_reports.templates');
            Route::post('/', [SuppEquipReportController::class, 'store'])->name('api.inventory.supp_equip_reports.store');
            Route::put('/{id?}', [SuppEquipReportController::class, 'update'])->name('api.inventory.supp_equip_reports.update');
            Route::delete('/{id?}', [SuppEquipReportController::class, 'destroy'])->name('api.inventory.supp_equip_reports.destroy');
        });
    });
});