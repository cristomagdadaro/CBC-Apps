<?php

use App\Http\Controllers\EventSubformController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\PDFGeneratorController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::prefix('forms')->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FORMS])
        ->get('/event/{event?}', [FormController::class, 'formGuestView'])
        ->name('forms.guest.index');

    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FORMS])
        ->get('/{id}/pdf', [PDFGeneratorController::class, 'downloadPdf'])
        ->name('forms.generate.pdf');
});

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('apps')->group(function () {
        Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FORMS])->prefix('event-forms')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Forms/FormIndex');
            })->name('forms.index');

            Route::get('/create', function () {
                return Inertia::render('Forms/FormCreate');
            })->name('forms.create');

            Route::get('/builder/{templateId?}', function ($templateId = null) {
                return Inertia::render('Forms/FormBuilder', [
                    'templateId' => $templateId,
                ]);
            })->name('forms.builder');

            Route::get('/scan/{event_id?}', function ($event_id = null) {
                return Inertia::render('Forms/FormScan', [
                    'event_id' => $event_id,
                ]);
            })->name('forms.scan');

            Route::get('/update/{event_id?}', [EventSubformController::class, 'show'])->name('forms.update');
            Route::get('/{event_id}', [FormController::class, 'show'])->name('forms.show');
            Route::post('/{event_id}/requirements', [FormController::class, 'updateRequirements'])->name('forms.requirements.update');
        });

        Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FORMS])->prefix('certificates')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Certificates/CertificateGenerator');
            })->name('certificates.index');
        });
    });
});