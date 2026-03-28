<?php

use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_INVENTORY])->prefix('file-report')->group(function () {
    Route::get('/create-guest/{barcode?}', function ($barcode = null) {
        return Inertia::render('Inventory/SuppEquipReports/SuppEquipReportsCreateGuest', [
            'reportTemplates' => config('suppequipreportforms'),
            'barcode' => $barcode,
        ]);
    })->name('suppEquipReports.create.guest');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('apps')->group(function () {
        Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_INVENTORY])->prefix('file-report')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Inventory/SuppEquipReports/SuppEquipReportsIndex');
            })->name('suppEquipReports.index');

            Route::get('/create', function () {
                return Inertia::render('Inventory/SuppEquipReports/SuppEquipReportsCreate', [
                    'reportTemplates' => config('suppequipreportforms'),
                ]);
            })->name('suppEquipReports.create');
        });
    });
});