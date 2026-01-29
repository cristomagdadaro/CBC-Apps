<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SuppEquipReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use App\Models\Category;

Route::prefix('inventory')->group(function () {
    Route::get('categories', [CategoryController::class, 'index'])->name('api.inventory.categories.index');

    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('api.inventory.transactions.index');
        Route::post('/', [TransactionController::class, 'create'])->name('api.inventory.transactions.store');
        Route::delete('/{id?}', [TransactionController::class, 'destroy'])->name('api.inventory.transactions.destroy');
        Route::delete('/multi/delete', [TransactionController::class, 'multiDestroy'])->name('api.inventory.transactions.multi-destroy');
        Route::put('/{id?}', [TransactionController::class, 'update'])->name('api.inventory.transactions.update');
        Route::get('/generate-barcode/{room?}', [TransactionController::class, 'generateUniqueBarcode128ID'])->name('api.inventory.transactions.genbarcode');
        Route::put('/outgoingStore/{id?}', [TransactionController::class, 'outgoingStockStore'])->name('api.inventory.transactions.outgoing');
    });

    Route::prefix('supp-equip-reports')->group(function () {
        Route::get('/', [SuppEquipReportController::class, 'index'])->name('api.inventory.supp_equip_reports.index');
        Route::get('/templates', [SuppEquipReportController::class, 'templates'])->name('api.inventory.supp_equip_reports.templates');
        Route::post('/', [SuppEquipReportController::class, 'store'])->name('api.inventory.supp_equip_reports.store');
        Route::put('/{id?}', [SuppEquipReportController::class, 'update'])->name('api.inventory.supp_equip_reports.update');
        Route::delete('/{id?}', [SuppEquipReportController::class, 'destroy'])->name('api.inventory.supp_equip_reports.destroy');
    });

    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index'])->name('api.inventory.items.index');
        Route::get('/options', [ItemController::class, 'indexOptions'])->name('api.inventory.items.options');
        Route::post('/', [ItemController::class, 'create'])->name('api.inventory.items.store');
        Route::delete('/{id?}', [ItemController::class, 'destroy'])->name('api.inventory.items.destroy');
        Route::put('/{id?}', [ItemController::class, 'update'])->name('api.inventory.items.update');
    });

    Route::prefix('personnels')->group(function () {
        Route::get('/', [PersonnelController::class, 'index'])->name('api.inventory.personnels.index');
        Route::post('/', [PersonnelController::class, 'create'])->name('api.inventory.personnels.store');
        Route::put('/{id?}', [PersonnelController::class, 'update'])->name('api.inventory.personnels.update');
        Route::delete('/{id?}', [PersonnelController::class, 'destroy'])->name('api.inventory.personnels.destroy');
    });

    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index'])->name('api.inventory.suppliers.index');
        Route::post('/', [SupplierController::class, 'create'])->name('api.inventory.suppliers.store');
        Route::put('/{id?}', [SupplierController::class, 'update'])->name('api.inventory.suppliers.update');
        Route::delete('/{id?}', [SupplierController::class, 'destroy'])->name('api.inventory.suppliers.destroy');
    });

});