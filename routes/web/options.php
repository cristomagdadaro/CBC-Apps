<?php

use App\Models\Option;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('apps')->group(function () {
        Route::prefix('system')->group(function () {
            Route::middleware(['can:event.forms.manage', 'deployment.access:' . DeploymentAccessService::MODULE_OPTIONS])->prefix('options')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('System/Options/OptionsIndex', [
                        'options' => Option::all(),
                    ]);
                })->name('system.options.index');

                Route::get('/create', function () {
                    return Inertia::render('System/Options/OptionUpsert');
                })->name('system.options.create');

                Route::get('/{id}', function () {
                    return Inertia::render('System/Options/OptionUpsert', [
                        'data' => Option::query()->findOrFail(request()->route('id')),
                    ]);
                })->name('system.options.show');
            });
        });
    });
});