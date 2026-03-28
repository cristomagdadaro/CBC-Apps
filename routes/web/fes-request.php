<?php

use App\Http\Controllers\LabRequestFormController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('forms')->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FES])
        ->get('/request-to-use/{request?}', [LabRequestFormController::class, 'labReqFormGuestView'])
        ->name('labReq.guest.index');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('apps')->group(function () {
        Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FES])->prefix('access-use-requests')->group(function () {
            Route::get('/', function () {
                return Inertia::render('LabRequest/AccessUseRequestsIndex');
            })->name('accessUseRequest.index');
        });
    });
});