<?php

use App\Http\Controllers\OptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Options Routes
|--------------------------------------------------------------------------
|
| Routes for system options management
|
*/

Route::prefix('options')->group(function () {
    // Specific named routes must come before generic {id} routes to prevent conflicts
    Route::get('/workflow-toggles', [OptionController::class, 'getWorkflowToggles'])
        ->name('api.options.workflow-toggles');

    Route::put('/workflow-toggles', [OptionController::class, 'updateWorkflowToggles'])
        ->middleware(['auth:sanctum', 'can:event.forms.manage'])
        ->name('api.options.workflow-toggles.update');

    // Get options by group
    Route::get('/group/{group}', [OptionController::class, 'getByGroup'])
        ->name('api.options.group');
    
    // Get option by key
    Route::get('/key/{key}', [OptionController::class, 'getByKey'])
        ->name('api.options.key');
    
    // Get all options grouped
    Route::get('/grouped/all', [OptionController::class, 'getAllGrouped'])
        ->name('api.options.grouped');
    
    // Get options for dropdown
    Route::get('/dropdown/list', [OptionController::class, 'getForDropdown'])
        ->name('api.options.dropdown');
    
    // Get options with metadata
    Route::get('/metadata/list', [OptionController::class, 'getWithMetadata'])
        ->name('api.options.metadata');
    
    // Get options for select fields by type (unified endpoint)
    Route::get('/select/{type}', [OptionController::class, 'getOptionsForSelect'])
        ->name('api.options.select');

    // Generic CRUD routes with {id} parameter (must come last to avoid conflicts with named routes)
    Route::get('/', [OptionController::class, 'index'])
        ->name('api.options.index');
    
    Route::post('/', [OptionController::class, 'store'])
        ->name('api.options.store');
    
    Route::put('/{id}', [OptionController::class, 'update'])
        ->name('api.options.update');
    
    Route::delete('/{id}', [OptionController::class, 'destroy'])
        ->name('api.options.destroy');
});
