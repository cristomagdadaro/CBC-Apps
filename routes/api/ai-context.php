<?php

use App\Http\Controllers\AI\AiContextController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SproutAi Internal Context Endpoints
|--------------------------------------------------------------------------
| Called by SproutAi server during sync runs to pull structured context.
| Protected by a shared bearer token (SPROUTAI_INTERNAL_SYNC_TOKEN).
*/

Route::prefix('internal/ai-context')
    ->middleware('sproutai.token')
    ->group(function () {
        Route::get('/inventory', [AiContextController::class, 'inventory'])
            ->name('ai-context.inventory');
    });
