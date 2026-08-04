<?php

use App\Http\Controllers\PollenAnalysisController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'deployment.access:pollen_analysis'])
    ->prefix('laboratory/pollen-counter')
    ->name('pollen_analysis.')
    ->group(function () {
        Route::get('/', [PollenAnalysisController::class, 'index'])->name('index');
        Route::post('/analyze', [PollenAnalysisController::class, 'analyze'])->name('analyze');
        Route::get('/{pollen_analysis}/image', [PollenAnalysisController::class, 'image'])->name('image');
    });
