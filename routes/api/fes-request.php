<?php

use App\Http\Controllers\RequestFormPivotController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;

Route::prefix('guest')->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FES])
        ->post('/', [RequestFormPivotController::class, 'create'])
        ->name('api.requestFormPivot.post');
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::middleware(['can:fes.request.approve', 'role.any:admin'])->group(function () {
        Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FES])->prefix('forms/use-request-form')->group(function () {
            Route::get('/', [RequestFormPivotController::class, 'index'])
                ->name('api.requestFormPivot.index');

            Route::put('/update/{request_pivot_id?}', [RequestFormPivotController::class, 'update'])
                ->name('api.requestFormPivot.put');
        });
    });
});