<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LabRequestController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
    Route::prefix('forms')->group(function () {
        Route::get('/{event_id?}', [FormController::class, 'index'])->name('api.form.guest.index');
        Route::middleware(['check.form.suspended','check.form.expired','check.form.maxslot'])->post('/registration/{event_id?}', [ParticipantController::class, 'post'])->name('api.form.registration.post');
    });

});


Route::middleware(['api','auth:sanctum','verified'])->group(function () {
    Route::prefix('forms')->group(function () {
        Route::prefix('event')->group(function () {
            Route::get('/', [FormController::class, 'index'])->name('api.form.index');
            Route::get('/{event_id?}', [FormController::class, 'show'])->name('api.form.show');
            Route::post('/create', [FormController::class, 'create'])->name('api.form.post');
            Route::delete('/delete/{event_id?}', [FormController::class, 'delete'])->name('api.form.delete');
            Route::middleware(['check.form.suspended'])->put('/update/{event_id?}', [FormController::class, 'update'])->name('api.form.put');
        });

        Route::prefix('lab-request')->group(function () {
            Route::post('/create', [LabRequestController::class, 'create'])->name('api.labReq.post');
        });
    });

    Route::prefix('inventory')->group(function () {
        Route::get('categories', function () {
            return response()->json([
                'data' => Category::all()
            ]);
        })->name('api.inventory.categories.index');

        Route::prefix('transactions')->group(function () {
            Route::get('/', [TransactionController::class, 'index'])->name('api.inventory.transactions.index');
            Route::post('/', [TransactionController::class, 'create'])->name('api.inventory.transactions.store');
            Route::delete('/{id}', [TransactionController::class, 'destroy'])->name('api.inventory.transactions.destroy');
            Route::delete('/multi/delete', [TransactionController::class, 'multiDestroy'])->name('api.inventory.transactions.multi-destroy');
            Route::put('/{id}', [TransactionController::class, 'update'])->name('api.inventory.transactions.update');
            Route::get('/generate-barcode/{room}', [TransactionController::class, 'generateUniqueBarcode128ID'])->name('api.inventory.transactions.genbarcode');
            Route::get('/remaining-stocks', [TransactionController::class, 'remainingStocks'])->name('api.inventory.transactions.remaining-stocks');
            Route::put('/outgoingStore/{id}', [TransactionController::class, 'outgoingStockStore'])->name('api.inventory.transactions.outgoing');
        });

        Route::prefix('items')->group(function () {
            Route::get('/', [ItemController::class, 'index'])->name('api.inventory.items.index');
            Route::post('/', [ItemController::class, 'create'])->name('api.inventory.items.store');
            Route::delete('/{id}', [ItemController::class, 'destroy'])->name('api.inventory.items.destroy');
            Route::put('/{id}', [ItemController::class, 'update'])->name('api.inventory.items.update');
        });

        Route::prefix('personnels')->group(function () {
            Route::get('/', [PersonnelController::class, 'index'])->name('api.inventory.personnels.index');
            Route::post('/', [PersonnelController::class, 'create'])->name('api.inventory.personnels.store');
            Route::put('/{id}', [PersonnelController::class, 'update'])->name('api.inventory.personnels.update');
            Route::delete('/{id}', [PersonnelController::class, 'destroy'])->name('api.inventory.personnels.destroy');
        });

        Route::prefix('suppliers')->group(function () {
            Route::get('/', [SupplierController::class, 'index'])->name('api.inventory.suppliers.index');
            Route::post('/', [SupplierController::class, 'create'])->name('api.inventory.suppliers.store');
            Route::put('/{id}', [SupplierController::class, 'update'])->name('api.inventory.suppliers.update');
            Route::delete('/{id}', [SupplierController::class, 'destroy'])->name('api.inventory.suppliers.destroy');
        });

    });
});
