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
    Route::get('/', [OptionController::class, 'index'])
        ->name('api.options.index');
    
    Route::post('/', [OptionController::class, 'store'])
        ->name('api.options.store');
    
    Route::put('/{id}', [OptionController::class, 'update'])
        ->name('api.options.update');
    
    Route::delete('/{id}', [OptionController::class, 'destroy'])
        ->name('api.options.destroy');
    
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
});
