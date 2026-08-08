<?php

use App\Http\Controllers\DevPreviewController;
use Illuminate\Support\Facades\Route;

Route::get('/dev/views', [DevPreviewController::class, 'index'])->name('dev.views.index');
Route::get('/dev/views/show', [DevPreviewController::class, 'show'])->name('dev.views.show');
