<?php

use App\Repositories\SuppEquipReportRepo;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_INCIDENT_REPORTS])->prefix('file-report')->group(function () {
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
                return Inertia::render('Inventory/SuppEquipReports/SuppEquipReportsFormPage', [
                    'reportTemplates' => config('suppequipreportforms'),
                    'mode' => 'create',
                ]);
            })->name('suppEquipReports.create');

            Route::get('/show/{id}', function (string $id, SuppEquipReportRepo $repo) {
                return Inertia::render('Inventory/SuppEquipReports/SuppEquipReportsFormPage', [
                    'reportTemplates' => config('suppequipreportforms'),
                    'mode' => 'update',
                    'data' => $repo->getFormData($id),
                ]);
            })->name('suppEquipReports.show');
        });
    });
});
