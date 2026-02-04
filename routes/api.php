<?php

use App\Http\Controllers\EventSubformController;
use App\Http\Controllers\EventCertificateController;
use App\Http\Controllers\EventSubformResponseController;
use App\Http\Controllers\EventWorkflowController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormScanController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RequestFormPivotController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SuppEquipReportController;
use App\Http\Controllers\TransactionController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Item;
use Symfony\Contracts\EventDispatcher\Event;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::prefix('guest')->group(function () {
    Route::prefix('locations')->group(function () {
        Route::get('/regions', [LocationController::class, 'regions'])->name('api.locations.regions');
        Route::get('/provinces', [LocationController::class, 'provinces'])->name('api.locations.provinces');
        Route::get('/cities', [LocationController::class, 'cities'])->name('api.locations.cities');
    });
    Route::prefix('forms')->group(function () {
        Route::get('/{event_id?}', [FormController::class, 'index'])->name('api.form.guest.index');
        Route::middleware(['check.form.suspended','check.form.expired','check.form.maxslot'])->post('/registration/{event_id?}', [ParticipantController::class, 'post'])->name('api.form.registration.post');
    });

    Route::post('/', [RequestFormPivotController::class, 'create'])->name('api.requestFormPivot.post');
    
    Route::get('/personnel/public', [PersonnelController::class, 'index'])->name('api.inventory.personnels.index.guest');
    Route::get('/items/public', function () {
        return ['data' => Item::selectRaw("CONCAT(name, IF(description IS NOT NULL AND description != '', CONCAT(' (', description, ')'), '')) AS label, id AS name")->where('category_id', 6)->get()];
    })->name('api.inventory.items.public');
    Route::get('/equipments/public', function () {
        return [ 'data' => Item::select('name as label', 'id as name')->where('category_id', '7')->get()];
    })->name('api.inventory.equipments.public');
    Route::get('/laboratories/public', function () {
        return [ 'data' => config('system.laboratories')];
    })->name('api.inventory.laboratories.public');
    Route::get('/transactions-public', [TransactionController::class, 'index'])->name('api.inventory.transactions.index.public');
    Route::post('/outgoing', [TransactionController::class, 'outgoingStockStore'])->name('api.inventory.transactions.store.public');
    Route::get('/remaining-stocks', [TransactionController::class, 'remainingStocks'])->name('api.inventory.transactions.remaining-stocks');
    Route::get('/forms/event/{event_id}/workflow', [EventWorkflowController::class, 'state'])->name('api.event.workflow.state.guest');
    Route::middleware(['check.form.suspended','check.form.expired','check.form.maxslot'])->post('/forms/event', [EventSubformResponseController::class, 'create'])->name('api.subform.response.store');
});

/* 'auth:sanctum', */
Route::middleware(['api'])->group(function () {
    Route::prefix('locations')->group(function () {
        Route::get('/regions', [LocationController::class, 'regions'])->name('api.locations.regions.auth');
        Route::get('/provinces', [LocationController::class, 'provinces'])->name('api.locations.provinces.auth');
        Route::get('/cities', [LocationController::class, 'cities'])->name('api.locations.cities.auth');
    });
    Route::prefix('forms')->group(function () {
        Route::prefix('event')->group(function () {
            Route::get('/', [FormController::class, 'index'])->name('api.form.index');
            Route::get('/participants/{event_id?}', [FormController::class, 'indexParticipants'])->name('api.form.participants.index');
            Route::delete('/participants/{partcipant_id}', [FormController::class, 'deleteParticipants'])->name('api.form.participants.delete');
            Route::get('/{event_id?}', [FormController::class, 'show'])->name('api.form.show');
            Route::post('/create', [FormController::class, 'create'])->name('api.form.post');
            Route::delete('/delete/{event_id?}', [FormController::class, 'delete'])->name('api.form.delete');
            Route::middleware(['check.form.suspended'])->put('/update/{event_id?}', action: [FormController::class, 'update'])->name('api.form.put');
            Route::get('/responses/{event_id?}', [EventSubformResponseController::class, 'index'])->name('api.subform.response.index');
            Route::get('/workflow/{event_id}', [EventWorkflowController::class, 'state'])->name('api.event.workflow.state');
            Route::put('/responses/{event_id?}', [EventSubformResponseController::class, 'update'])->name('api.subform.response.put');
            Route::delete('/responses/{response_id}', [EventSubformResponseController::class, 'delete'])->name('api.subform.response.delete');
            Route::get('/requirements/{event_id?}', [EventSubformController::class, 'index'])->name('api.subform.requirement.index');
            Route::post('/certificates/{event_id}/template', [EventCertificateController::class, 'uploadTemplate'])->name('api.event.certificates.template.upload');
            Route::post('/certificates/{event_id}/generate', [EventCertificateController::class, 'generate'])->name('api.event.certificates.generate');
            Route::post('/{event_id}/scan', [FormScanController::class, 'scan'])->name('api.form.scan');
        });

        Route::prefix('use-request-form')->group(function () {
            Route::get('/', [RequestFormPivotController::class, 'index'])->name('api.requestFormPivot.index');
            Route::put('/update/{request_pivot_id?}', [RequestFormPivotController::class, 'update'])->name('api.requestFormPivot.put');
        });
    });

    require __DIR__.'/inventory.php';
});
