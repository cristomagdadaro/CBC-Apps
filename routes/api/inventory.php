<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Repositories\OptionRepo;
use App\Repositories\TransactionRepo;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;

Route::prefix('guest')->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_INVENTORY])
        ->get('/personnel/public', [PersonnelController::class, 'publicLookup'])
        ->name('api.inventory.personnels.index.guest');
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_EQUIPMENT_LOGGER])
        ->post('/personnel/public/initialize-profile', [PersonnelController::class, 'initializeProfile'])
        ->name('api.inventory.personnels.initialize-profile.guest');

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

                $stocks = app(TransactionRepo::class)
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

                $stocks = app(TransactionRepo::class)
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
});

Route::middleware(['api', 'auth:sanctum'])->group(function () {
    Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_INVENTORY])->prefix('inventory')->group(function () {
        Route::middleware(['can:inventory.manage'])->group(function () {
            Route::get('categories', [CategoryController::class, 'index'])->name('api.inventory.categories.index');

            Route::prefix('transactions')->group(function () {
                Route::get('/', [TransactionController::class, 'index'])->name('api.inventory.transactions.index');
                Route::get('/dashboard', [TransactionController::class, 'dashboard'])->name('api.inventory.transactions.dashboard');
                Route::get('/recounting/lookup', [TransactionController::class, 'recountLookup'])->name('api.inventory.transactions.recounting.lookup');
                Route::post('/recounting/adjust', [TransactionController::class, 'recountAdjust'])->name('api.inventory.transactions.recounting.adjust');
                Route::post('/', [TransactionController::class, 'create'])->name('api.inventory.transactions.store');
                Route::delete('/{id?}', [TransactionController::class, 'destroy'])->name('api.inventory.transactions.destroy');
                Route::delete('/multi/delete', [TransactionController::class, 'multiDestroy'])->name('api.inventory.transactions.multi-destroy');
                Route::put('/{id?}', [TransactionController::class, 'update'])->name('api.inventory.transactions.update');
                Route::get('/generate-barcode/{room?}', [TransactionController::class, 'generateUniqueBarcode128ID'])->name('api.inventory.transactions.genbarcode');
                Route::put('/outgoingStore/{id?}', [TransactionController::class, 'outgoingStockStore'])->name('api.inventory.transactions.outgoing');
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
    });
});
