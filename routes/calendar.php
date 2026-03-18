<?php

use App\Http\Controllers\GoogleCalendarController;
use Illuminate\Support\Facades\Route;

Route::prefix('google-calendar')->group(function () {
    Route::get('/', [GoogleCalendarController::class, 'index'])->name('api.google-calendar.index');
    Route::post('/sync', [GoogleCalendarController::class, 'sync'])->name('api.google-calendar.sync');
    Route::post('/sync-batch', [GoogleCalendarController::class, 'syncBatch'])->name('api.google-calendar.sync-batch');
    Route::post('/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('api.google-calendar.disconnect');
});