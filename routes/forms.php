<?php

use App\Http\Controllers\EventCertificateController;
use App\Http\Controllers\EventSubformController;
use App\Http\Controllers\EventSubformResponseController;
use App\Http\Controllers\EventWorkflowController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormScanController;
use App\Http\Controllers\RequestFormPivotController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Forms Routes
|--------------------------------------------------------------------------
|
| Routes for event forms and use request forms management
|
*/

Route::middleware(['api', 'auth'])->prefix('forms')->group(function () {
    // Event forms
    Route::prefix('event')->group(function () {
        Route::get('/', [FormController::class, 'index'])
            ->name('api.form.index');
        
        Route::get('/participants/{event_id?}', [FormController::class, 'indexParticipants'])
            ->name('api.form.participants.index');
        
        Route::delete('/participants/{partcipant_id}', [FormController::class, 'deleteParticipants'])
            ->name('api.form.participants.delete');
        
        Route::get('/{event_id?}', [FormController::class, 'show'])
            ->name('api.form.show');
        
        Route::post('/create', [FormController::class, 'create'])
            ->name('api.form.post');
        
        Route::delete('/delete/{event_id?}', [FormController::class, 'delete'])
            ->name('api.form.delete');
        
        Route::middleware(['check.form.suspended'])
            ->put('/update/{event_id?}', [FormController::class, 'update'])
            ->name('api.form.put');
        
        // Event responses
        Route::get('/responses/{event_id?}', [EventSubformResponseController::class, 'index'])
            ->name('api.subform.response.index');
        
        Route::put('/responses/{event_id?}', [EventSubformResponseController::class, 'update'])
            ->name('api.subform.response.put');
        
        Route::delete('/responses/{response_id}', [EventSubformResponseController::class, 'delete'])
            ->name('api.subform.response.delete');
        
        // Event workflow
        Route::get('/workflow/{event_id}', [EventWorkflowController::class, 'state'])
            ->name('api.event.workflow.state');
        
        // Event requirements/subforms
        Route::get('/requirements/{event_id?}', [EventSubformController::class, 'index'])
            ->name('api.subform.requirement.index');
        
        // Event certificates
        Route::post('/certificates/{event_id}/template', [EventCertificateController::class, 'uploadTemplate'])
            ->name('api.event.certificates.template.upload');
        
        Route::post('/certificates/{event_id}/generate', [EventCertificateController::class, 'generate'])
            ->name('api.event.certificates.generate');
        
        // Form scanning
        Route::post('/{event_id}/scan', [FormScanController::class, 'scan'])
            ->name('api.form.scan');
    });

    // Use request forms
    Route::prefix('use-request-form')->group(function () {
        Route::get('/', [RequestFormPivotController::class, 'index'])
            ->name('api.requestFormPivot.index');
        
        Route::put('/update/{request_pivot_id?}', [RequestFormPivotController::class, 'update'])
            ->name('api.requestFormPivot.put');
    });
});
