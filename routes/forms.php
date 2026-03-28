<?php

use App\Http\Controllers\EventCertificateController;
use App\Http\Controllers\EventSubformController;
use App\Http\Controllers\EventSubformResponseController;
use App\Http\Controllers\EventWorkflowController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormScanController;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Forms Routes
|--------------------------------------------------------------------------
|
| Routes for event forms and use request forms management
|
*/

Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FORMS])->prefix('forms')->group(function () {

    // Form Builder API (templates & field types)
    Route::prefix('builder')->middleware(['can:event.forms.manage'])->group(function () {
        // Field types (public reference)
        Route::get('/field-types', [FormBuilderController::class, 'fieldTypes'])
            ->name('api.form-builder.field-types');

        // Templates CRUD
        Route::get('/templates', [FormBuilderController::class, 'indexTemplates'])
            ->name('api.form-builder.templates.index');

        Route::get('/templates/slug/{slug}', [FormBuilderController::class, 'showTemplateBySlug'])
            ->name('api.form-builder.templates.by-slug');

        Route::get('/templates/{id}', [FormBuilderController::class, 'showTemplate'])
            ->name('api.form-builder.templates.show');

        Route::post('/templates', [FormBuilderController::class, 'storeTemplate'])
            ->name('api.form-builder.templates.store');

        Route::put('/templates/{id}', [FormBuilderController::class, 'updateTemplate'])
            ->name('api.form-builder.templates.update');

        Route::delete('/templates/{id}', [FormBuilderController::class, 'destroyTemplate'])
            ->name('api.form-builder.templates.destroy');

        Route::post('/templates/{id}/duplicate', [FormBuilderController::class, 'duplicateTemplate'])
            ->name('api.form-builder.templates.duplicate');

        // Template selection for dropdowns (lightweight)
        Route::get('/templates-select', [FormBuilderController::class, 'templatesForSelect'])
            ->name('api.form-builder.templates.select');

        // Preview validation rules for a template
        Route::get('/templates/{id}/preview-validation', [FormBuilderController::class, 'previewValidation'])
            ->name('api.form-builder.templates.preview-validation');

        // Assign template to event subform
        Route::post('/assign-template', [FormBuilderController::class, 'assignToEvent'])
            ->name('api.form-builder.assign-template');

        // Get resolved schema for event subform
        Route::get('/subform-schema/{subformId}', [FormBuilderController::class, 'eventSubformSchema'])
            ->name('api.form-builder.subform-schema');
    });
    // Event forms
    Route::prefix('event')->group(function () {
        Route::middleware(['can:event.forms.manage'])->group(function () {
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

            // Form scanning
            Route::post('/{event_id}/scan', [FormScanController::class, 'scan'])
                ->name('api.form.scan');
        });

        // Event certificates
        Route::middleware(['can:event.certificates.manage'])->group(function () {
            Route::post('/certificates/{event_id}/template', [EventCertificateController::class, 'uploadTemplate'])
                ->name('api.event.certificates.template.upload');

            Route::get('/certificates/{event_id}/template', [EventCertificateController::class, 'downloadTemplate'])
                ->name('api.event.certificates.template.view');

            Route::post('/certificates/{event_id}/generate', [EventCertificateController::class, 'generate'])
                ->name('api.event.certificates.generate');

            Route::get('/certificates/{event_id}/columns', [EventCertificateController::class, 'columns'])
                ->name('api.event.certificates.columns');

            Route::get('/certificates/{event_id}/status/{batch_id}', [EventCertificateController::class, 'status'])
                ->name('api.event.certificates.status');

            Route::get('/certificates/{event_id}/download/{batch_id}', [EventCertificateController::class, 'download'])
                ->name('api.event.certificates.download');
        });
    });

});
