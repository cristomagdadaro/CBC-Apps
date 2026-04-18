<?php

use App\Enums\Inventory;
use App\Http\Controllers\InventoryFormController;
use App\Http\Controllers\PDFGeneratorController;
use App\Models\Item;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Repositories\CategoryRepo;
use App\Repositories\OptionRepo;
use App\Repositories\SupplierRepo;
use App\Repositories\TransactionRepo;
use App\Services\DeploymentAccessService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT])
    ->get('/inventory/outgoing', [InventoryFormController::class, 'outgoingForm'])
    ->name('inventory.public.outgoing.index');

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::prefix('apps')->group(function () {
        Route::middleware(['deployment.access:' . DeploymentAccessService::MODULE_INVENTORY])->prefix('inventory')->group(function () {
            Route::prefix('items')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('Inventory/Items/Items', [
                        'fromUrl' => route('dashboard'),
                    ]);
                })->name('items.index');

                Route::get('/create', function () {
                    return Inertia::render('Inventory/Items/components/CreateItem', [
                        'fromUrl' => url()->previous(),
                        'suppliers' => app(SupplierRepo::class)->getOptions(),
                        'categories' => app(CategoryRepo::class)->getInventoryFormCategories(),
                    ]);
                })->name('items.create');

                Route::get('/{id}', function () {
                    return Inertia::render('Inventory/Items/components/EditItemForm', [
                        'data' => Item::find(request()->route('id')),
                        'suppliers' => app(SupplierRepo::class)->getOptions(),
                        'categories' => app(CategoryRepo::class)->getInventoryFormCategories(),
                        'fromUrl' => url()->previous(),
                    ]);
                })->name('items.show');
            });

            Route::get('/barcodes/print', function () {
                return Inertia::render('Inventory/Barcodes/BarcodePrint', [
                    'fromUrl' => route('items.index'),
                    'categories' => app(CategoryRepo::class)->getInventoryFormCategories(),
                    'storage_locations' => app(OptionRepo::class)->getStorageLocations(),
                ]);
            })->name('inventory.barcodes.print');

            Route::post('/barcodes/pdf', [PDFGeneratorController::class, 'generatePdf'])
                ->name('inventory.barcodes.pdf');

            Route::post('/generate-pdf', [PDFGeneratorController::class, 'generatePdf'])
                ->name('inventory.generate-pdf');

            Route::prefix('transactions')->group(function () {
                Route::get('/dashboard', function () {
                    return Inertia::render('Inventory/Transactions/InventoryDashboard', [
                        'fromUrl' => route('transactions.index'),
                    ]);
                })->name('transactions.dashboard');

                Route::get('/', function () {
                    return Inertia::render('Inventory/Transactions/Transactions', [
                        'fromUrl' => route('dashboard'),
                        'categories' => app(CategoryRepo::class)->getInventoryFormCategories(),
                    ]);
                })->name('transactions.index');

                Route::get('/incoming', function () {
                    return Inertia::render('Inventory/Transactions/components/Incoming', [
                        'fromUrl' => route('transactions.index'),
                        'items' => Item::get(),
                        'suppliers' => app(SupplierRepo::class)->getOptions(),
                        'categories' => app(CategoryRepo::class)->getInventoryFormCategories(),
                        'storage_locations' => app(OptionRepo::class)->getStorageLocations(),
                        'personnels' => Personnel::selectRaw('id, employee_id, fname, mname, lname, suffix')->whereNotIn('id', [1])->get(),
                    ]);
                })->name('transactions.incoming');

                Route::get('/outgoing', function () {
                    return Inertia::render('Inventory/Transactions/components/Outgoing', [
                        'fromUrl' => url()->previous(),
                        'personnels' => Personnel::selectRaw('id, employee_id, fname, mname, lname, suffix')->whereNotIn('id', [1])->get(),
                        'stockLevel' => app(OptionRepo::class)->getStockLevels(),
                        'categories' => app(CategoryRepo::class)->getInventoryFormCategories(),
                        'projectCodes' => app(TransactionRepo::class)->getAvailableProjectCodes(),
                        'storage_locations' => app(OptionRepo::class)->getStorageLocations(),
                    ]);
                })->name('transactions.outgoing');

                Route::get('/recounting', function () {
                    return Inertia::render('Inventory/Transactions/Recounting', [
                        'fromUrl' => route('transactions.index'),
                        'storage_locations' => app(OptionRepo::class)->getStorageLocations(),
                    ]);
                })->name('transactions.recounting');

                Route::get('/{id}', function () {
                    $transaction = Transaction::find(request()->route('id'));

                    if (! $transaction) {
                        return redirect()->route('transactions.index');
                    }

                    $transaction->load([
                        'item',
                        'user:id,name',
                        'personnel:id,employee_id,fname,mname,lname,suffix',
                        'components.componentTransaction.item',
                        'components.componentTransaction.user:id,name',
                        'components.componentTransaction.personnel:id,employee_id,fname,mname,lname,suffix',
                        'parentComponent.parentTransaction.item',
                        'parentComponent.parentTransaction.user:id,name',
                        'parentComponent.parentTransaction.personnel:id,employee_id,fname,mname,lname,suffix',
                    ]);

                    $attachedComponents = $transaction->components
                        ->map(fn ($link) => $link->componentTransaction)
                        ->filter()
                        ->values();

                    $parentTransaction = optional($transaction->parentComponent)->parentTransaction;

                    $attachedReports = $transaction->reports()
                        ->with([
                            'user:id,name',
                            'item:id,name,brand',
                            'transaction:id,barcode',
                        ])
                        ->orderByDesc('reported_at')
                        ->orderByDesc('created_at')
                        ->get();

                    if ($transaction->transac_type === Inventory::INCOMING->value) {
                        return Inertia::render('Inventory/Transactions/components/Incoming', [
                            'data' => $transaction,
                            'items' => Item::withTrashed()->get(),
                            'fromUrl' => route('transactions.index'),
                            'storage_locations' => app(OptionRepo::class)->getStorageLocations(),
                            'personnels' => Personnel::selectRaw('id, employee_id, fname, mname, lname, suffix')->whereNotIn('id', [1])->get(),
                            'attachedReports' => $attachedReports,
                            'attachedComponents' => $attachedComponents,
                            'parentTransaction' => $parentTransaction,
                        ]);
                    }

                    return Inertia::render('Inventory/Transactions/components/Outgoing', [
                        'data' => Transaction::select('*')->where('transactions.id', request()->route('id'))->first(),
                        'summary' => Transaction::selectRaw('
                                    items.id as item_id,
                                    items.name,
                                    items.brand,
                                    items.description,
                                    transactions.unit,
                                    transactions.barcode,
                                    transactions.barcode_prri,

                                    SUM(CASE WHEN transactions.transac_type = "incoming"
                                        THEN transactions.quantity ELSE 0 END) AS total_ingoing,

                                    SUM(CASE WHEN transactions.transac_type = "outgoing"
                                        THEN ABS(transactions.quantity) ELSE 0 END) AS total_outgoing,

                                    (
                                        SUM(CASE WHEN transactions.transac_type = "incoming"
                                            THEN transactions.quantity ELSE 0 END)
                                        -
                                        SUM(CASE WHEN transactions.transac_type = "outgoing"
                                            THEN ABS(transactions.quantity) ELSE 0 END)
                                    ) AS remaining_quantity')
                            ->join('items', 'transactions.item_id', '=', 'items.id')
                            ->where('transactions.item_id', $transaction->item_id)
                            ->groupBy('items.id', 'items.name', 'items.brand', 'transactions.unit', 'transactions.barcode', 'transactions.barcode_prri')
                            ->first(),
                        'mode' => 'update',
                        'fromUrl' => route('transactions.index'),
                        'personnels' => Personnel::selectRaw('id, employee_id, fname, mname, lname, suffix')->whereNotIn('id', [1])->get(),
                        'attachedReports' => $attachedReports,
                    ]);
                })->name('transactions.show');
            });

            Route::prefix('personnels')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('Inventory/Personnel/Personnel', [
                        'fromUrl' => route('dashboard'),
                    ]);
                })->name('personnels.index');

                Route::get('/create', function () {
                    return Inertia::render('Inventory/Personnel/components/CreatePersonnelForm', [
                        'fromUrl' => route('personnels.index'),
                        'nextExternalEmployeeId' => app(\App\Repositories\PersonnelRepo::class)->previewNextExternalEmployeeId(),
                    ]);
                })->name('personnels.create');

                Route::get('/{id}', function () {
                    return Inertia::render('Inventory/Personnel/components/EditPersonnelForm', [
                        'data' => Personnel::find(request()->route('id')),
                        'fromUrl' => route('personnels.index'),
                    ]);
                })->name('personnels.show');
            });

            Route::prefix('suppliers')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('Inventory/Supplier/Supplier', [
                        'fromUrl' => route('dashboard'),
                    ]);
                })->name('suppliers.index');

                Route::get('/create', function () {
                    return Inertia::render('Inventory/Supplier/components/CreateSupplierForm', [
                        'fromUrl' => url()->previous(),
                    ]);
                })->name('suppliers.create');

                Route::get('/{id}', function () {
                    return Inertia::render('Inventory/Supplier/components/EditSupplierForm', [
                        'data' => Supplier::find(request()->route('id')),
                        'fromUrl' => url()->previous(),
                    ]);
                })->name('suppliers.show');
            });
        });
    });
});
