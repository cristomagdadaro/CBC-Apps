<?php

use App\Http\Controllers\GoLinkController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_GOLINK])
        ->prefix('go-links')
        ->group(function () {
            Route::middleware(['can:golinks.manage'])->group(function () {
                Route::get('/', [GoLinkController::class, 'index'])->name('api.golinks.index');
                Route::post('/', [GoLinkController::class, 'create'])->name('api.golinks.store');
                Route::put('/{id?}', [GoLinkController::class, 'update'])->name('api.golinks.update');
                Route::delete('/{id?}', [GoLinkController::class, 'destroy'])->name('api.golinks.destroy');
            });
        });
});
