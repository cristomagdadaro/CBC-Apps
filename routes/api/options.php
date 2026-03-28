<?php

use App\Http\Controllers\OptionController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;

Route::prefix('options')->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_OPTIONS])
        ->get('/workflow-toggles', [OptionController::class, 'getWorkflowToggles'])
        ->name('api.options.workflow-toggles');
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::prefix('options')->group(function () {
        Route::middleware(['auth:sanctum', 'can:event.forms.manage', 'deployment.access:' . DeploymentAccessService::MODULE_OPTIONS])->group(function () {
            Route::put('/workflow-toggles', [OptionController::class, 'updateWorkflowToggles'])
                ->name('api.options.workflow-toggles.update');

            Route::get('/deployment-access', [OptionController::class, 'getDeploymentAccess'])
                ->name('api.options.deployment-access');

            Route::put('/deployment-access', [OptionController::class, 'updateDeploymentAccess'])
                ->name('api.options.deployment-access.update');

            Route::get('/group/{group}', [OptionController::class, 'getByGroup'])
                ->name('api.options.group');

            Route::get('/key/{key}', [OptionController::class, 'getByKey'])
                ->name('api.options.key');

            Route::get('/grouped/all', [OptionController::class, 'getAllGrouped'])
                ->name('api.options.grouped');

            Route::get('/dropdown/list', [OptionController::class, 'getForDropdown'])
                ->name('api.options.dropdown');

            Route::get('/metadata/list', [OptionController::class, 'getWithMetadata'])
                ->name('api.options.metadata');

            Route::get('/select/{type}', [OptionController::class, 'getOptionsForSelect'])
                ->name('api.options.select');

            Route::get('/', [OptionController::class, 'index'])
                ->name('api.options.index');

            Route::post('/', [OptionController::class, 'store'])
                ->name('api.options.store');

            Route::put('/{id}', [OptionController::class, 'update'])
                ->name('api.options.update');

            Route::delete('/{id}', [OptionController::class, 'destroy'])
                ->name('api.options.destroy');
        });
    });
});