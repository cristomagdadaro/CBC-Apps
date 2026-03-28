<?php

use App\Http\Controllers\EventSubformResponseController;
use App\Http\Controllers\EventWorkflowController;
use App\Http\Controllers\FormController;
use App\Services\DeploymentAccessService;
use App\Http\Controllers\RentalVehicleController;
use App\Http\Controllers\RentalVenueController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\RequestFormPivotController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ICTEquipmentController;
use App\Repositories\OptionRepo;
use App\Http\Controllers\LaboratoryEquipmentController;
use App\Models\Personnel;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleCalendarController;
/*
|--------------------------------------------------------------------------
| Guest Routes
|--------------------------------------------------------------------------
|
| These routes are publicly accessible without authentication
|
*/

Route::prefix('guest')->group(function () {
    // Location endpoints
    Route::prefix('locations')->group(function () {
        Route::get('/regions', [LocationController::class, 'regions'])->name('api.locations.regions');
        Route::get('/provinces', [LocationController::class, 'provinces'])->name('api.locations.provinces');
        Route::get('/cities', [LocationController::class, 'cities'])->name('api.locations.cities');
    });

    // Event form endpoints
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FORMS])->prefix('forms')->group(function () {
        Route::get('/{event_id?}', [FormController::class, 'index'])->name('api.form.guest.index');
        Route::middleware(['check.form.suspended','check.form.expired','check.form.maxslot'])
            ->post('/registration/{event_id?}', [ParticipantController::class, 'post'])
            ->name('api.form.registration.post');
    });

    // Request form endpoints
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FES])
        ->post('/', [RequestFormPivotController::class, 'create'])->name('api.requestFormPivot.post');

    // Personnel endpoints
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_INVENTORY])->get('/personnel/public', function () {
        $personnels = Personnel::query()
            ->select(['id', 'fname', 'mname', 'lname', 'suffix', 'position'])
            ->orderBy('lname')
            ->orderBy('fname')
            ->limit(100)
            ->get()
            ->map(function (Personnel $personnel) {
                $fullName = trim(implode(' ', array_filter([
                    $personnel->fname,
                    $personnel->mname,
                    $personnel->lname,
                    $personnel->suffix,
                ])));

                return [
                    'id' => $personnel->id,
                    'name' => $fullName,
                    'position' => $personnel->position,
                ];
            });

        return response()->json(['data' => $personnels]);
    })->name('api.inventory.personnels.index.guest');

    // Inventory public endpoints
    Route::prefix('inventory')->group(function () {
        Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_INVENTORY])->group(function () {
            Route::get('/category/{categoryName?}', [TransactionController::class, 'getRemainingStocksPerCategory'])->name('api.inventory.categories.public');

            Route::get('/items/public', function () {
                $minRemaining = request()->query('min_remaining', 0);
                $params = collect([
                    'filter' => 'category',
                    'filter_by' => 6,
                    'min_remaining' => $minRemaining,
                    'paginate' => false,
                    'per_page' => '*',
                ]);

                $stocks = app(\App\Repositories\TransactionRepo::class)
                    ->getRemainingStocks($params)
                    ->get('data', collect());

                $items = collect($stocks)->map(function ($row) {
                    $baseLabel = $row->name . ($row->description ? " ({$row->description})" : '');
                    $stockInfo = $row->remaining_quantity !== null
                        ? " - {$row->remaining_quantity}" . ($row->unit ? " {$row->unit}" : '')
                        : '';

                    return [
                        'value' => $baseLabel,
                        'label' => $baseLabel . $stockInfo,
                    ];
                })->values();

                return ['data' => $items];
            })->name('api.inventory.items.public');

            Route::get('/equipments/public', function () {
                $minRemaining = request()->query('min_remaining', 0);
                $params = collect([
                    'filter' => 'category',
                    'filter_by' => [4, 5],
                    'min_remaining' => $minRemaining,
                    'paginate' => false,
                    'per_page' => '*',
                ]);

                $stocks = app(\App\Repositories\TransactionRepo::class)
                    ->getRemainingStocks($params)
                    ->get('data', collect());

                $items = collect($stocks)->map(function ($row) {
                    $baseLabel = $row->name . ($row->description ? " ({$row->description})" : '');
                    $stockInfo = $row->remaining_quantity !== null
                        ? " - {$row->remaining_quantity}" . ($row->unit ? " {$row->unit}" : '')
                        : '';

                    return [
                        'value' => $baseLabel,
                        'label' => $baseLabel . $stockInfo,
                    ];
                })->values();

                return ['data' => $items];
            })->name('api.inventory.equipments.public');

            Route::get('/laboratories/public', function () {
                return ['data' => app(OptionRepo::class)->getLaboratories()];
            })->name('api.inventory.laboratories.public');
        });

        Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT])->group(function () {
            Route::get('/transactions-public', [TransactionController::class, 'index'])->name('api.inventory.transactions.index.public');
            Route::post('/outgoing', [TransactionController::class, 'outgoingStockStore'])->name('api.inventory.transactions.store.public');
            Route::get('/remaining-stocks', [TransactionController::class, 'remainingStocks'])->name('api.inventory.transactions.remaining-stocks');
            Route::get('/project-codes', [TransactionController::class, 'projectCodes'])->name('api.inventory.transactions.project-codes');
        });
    });

    // Rental public endpoints
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_RENTALS])->prefix('rental')->group(function () {
        Route::prefix('vehicles')->group(function () {
            Route::get('/', [RentalVehicleController::class, 'publicIndex'])->name('api.guest.rental.vehicles.index');
            Route::get('/{id}', [RentalVehicleController::class, 'publicShow'])->name('api.guest.rental.vehicles.show');
            Route::post('/', [RentalVehicleController::class, 'store'])->name('api.guest.rental.vehicles.store');
            Route::get('/check-availability/{vehicleType}/{dateFrom}/{dateTo}', [RentalVehicleController::class, 'checkAvailability'])
                ->name('api.guest.rental.vehicles.check-availability');
        });

        Route::prefix('venues')->group(function () {
            Route::get('/', [RentalVenueController::class, 'publicIndex'])->name('api.guest.rental.venues.index');
            Route::get('/{id}', [RentalVenueController::class, 'publicShow'])->name('api.guest.rental.venues.show');
            Route::post('/', [RentalVenueController::class, 'store'])->name('api.guest.rental.venues.store');
            Route::get('/check-availability/{venueType}/{dateFrom}/{dateTo}', [RentalVenueController::class, 'checkAvailability'])
                ->name('api.guest.rental.venues.check-availability');
        });
    });

    // Event subform responses
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FORMS, 'check.form.suspended','check.form.expired','check.form.maxslot'])
        ->post('/forms/event', [EventSubformResponseController::class, 'create'])
        ->name('api.subform.response.store');

    // Event workflow
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FORMS])->get('/forms/event/{event_id}/workflow', [EventWorkflowController::class, 'state'])
        ->name('api.event.workflow.state.guest');
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_FORMS])->get('/forms/event/{event_id}/participant-lookup', [EventWorkflowController::class, 'resolveParticipantByEmail'])
        ->name('api.event.participant.lookup.guest');

    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_EQUIPMENT_LOGGER])->group(function () {
        Route::middleware(['auth:sanctum'])->get('/equipments/active/{employee_id?}', [LaboratoryEquipmentController::class, 'activeEquipments'])->name('api.laboratory.equipments.active');
        Route::get('/equipments/{identifier}', [LaboratoryEquipmentController::class, 'show'])->name('api.laboratory.equipments.show');
        Route::middleware(['auth:sanctum'])->post('/equipments/{identifier}/check-in', [LaboratoryEquipmentController::class, 'checkIn'])->name('api.laboratory.equipments.check-in');
        Route::middleware(['auth:sanctum'])->post('/equipments/{identifier}/check-out', [LaboratoryEquipmentController::class, 'checkOut'])->name('api.laboratory.equipments.check-out');
        Route::middleware(['auth:sanctum'])->post('/equipments/{identifier}/update-end-use', [LaboratoryEquipmentController::class, 'updateEndUse'])->name('api.laboratory.equipments.update-end-use');
        Route::middleware(['auth:sanctum'])->post('/equipments/{identifier}/report-location', [LaboratoryEquipmentController::class, 'reportLocation'])->name('api.laboratory.equipments.report-location');
        Route::get('/equipments', [LaboratoryEquipmentController::class, 'index'])->name('api.laboratory.equipments.index');

        Route::middleware(['auth:sanctum'])->get('/ict/equipments/active/{employee_id?}', [ICTEquipmentController::class, 'activeEquipments'])->name('api.ict.equipments.active');
        Route::get('/ict/equipments/{identifier}', [ICTEquipmentController::class, 'show'])->name('api.ict.equipments.show');
        Route::middleware(['auth:sanctum'])->post('/ict/equipments/{identifier}/check-in', [ICTEquipmentController::class, 'checkIn'])->name('api.ict.equipments.check-in');
        Route::middleware(['auth:sanctum'])->post('/ict/equipments/{identifier}/check-out', [ICTEquipmentController::class, 'checkOut'])->name('api.ict.equipments.check-out');
        Route::middleware(['auth:sanctum'])->post('/ict/equipments/{identifier}/update-end-use', [ICTEquipmentController::class, 'updateEndUse'])->name('api.ict.equipments.update-end-use');
        Route::middleware(['auth:sanctum'])->post('/ict/equipments/{identifier}/report-location', [ICTEquipmentController::class, 'reportLocation'])->name('api.ict.equipments.report-location');
        Route::get('/ict/equipments', [ICTEquipmentController::class, 'index'])->name('api.ict.equipments.index');
    });
});
