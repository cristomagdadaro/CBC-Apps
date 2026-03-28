<?php

use App\Http\Controllers\RequestFormPivotController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FES Request Approval Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FES])->prefix('forms/use-request-form')->group(function () {
    Route::get('/', [RequestFormPivotController::class, 'index'])
        ->name('api.requestFormPivot.index');

    Route::put('/update/{request_pivot_id?}', [RequestFormPivotController::class, 'update'])
        ->name('api.requestFormPivot.put');
});
