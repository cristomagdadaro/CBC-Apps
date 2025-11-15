<?php

use App\Enums\Inventory;
use App\Http\Controllers\FormController;
use App\Http\Controllers\InventoryFormController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LabRequestFormController;
use App\Http\Controllers\PDFGeneratorController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\RequesterController;
use App\Http\Controllers\RequestFormPivotController;
use App\Http\Controllers\SupplierController;
use App\Models\Category;
use App\Models\Form;
use App\Models\Item;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\Transaction;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::prefix('forms')->group(function () {
    Route::get('/event/{event?}', [FormController::class, 'formGuestView'])->name('forms.guest.index');
    Route::get('/request-to-use/{request?}', [LabRequestFormController::class, 'labReqFormGuestView'])->name('labReq.guest.index');
    Route::get('/inventory/outgoing', [InventoryFormController::class, 'outgoingForm'])->name('inventory.public.outgoing.index');
    Route::get('/{id}/pdf', [PDFGeneratorController::class, 'downloadPdf'])->name('forms.generate.pdf');

});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        $now = now();

        $stockBaseQuery = Transaction::selectRaw(
                'items.id as item_id,' .
                ' SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) as total_ingoing,' .
                ' SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity WHEN transactions.transac_type = "outgoing" THEN -transactions.quantity ELSE 0 END) as remaining_quantity'
            )
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->groupBy('items.id');

        $percentageExpr = 'CASE WHEN total_ingoing <> 0 THEN remaining_quantity / total_ingoing ELSE 0 END';

        $emptyStockCount = (clone $stockBaseQuery)
            ->havingRaw("$percentageExpr <= 0")
            ->count();

        $lowStockCount = (clone $stockBaseQuery)
            ->havingRaw("$percentageExpr > 0 AND $percentageExpr <= 0.25")
            ->count();

        $midStockCount = (clone $stockBaseQuery)
            ->havingRaw("$percentageExpr > 0.25 AND $percentageExpr <= 0.75")
            ->count();

        $highStockCount = (clone $stockBaseQuery)
            ->havingRaw("$percentageExpr > 0.75")
            ->count();

        $stats = [
            'events' => [
                'total'    => Form::count(),
                'active'   => Form::where('is_suspended', false)
                                   ->where('is_expired', false)
                                   ->count(),
                'upcoming' => Form::whereDate('date_from', '>=', $now->toDateString())
                                   ->where('is_expired', false)
                                   ->count(),
                'suspended'=> Form::where('is_suspended', true)->count(),
                'expired'  => Form::where('is_expired', true)->count(),
            ],
            'access_requests' => [
                'total'    => \App\Models\RequestFormPivot::count(),
                'pending'  => \App\Models\RequestFormPivot::where('request_status', 'pending')->count(),
                'approved' => \App\Models\RequestFormPivot::where('request_status', 'approved')->count(),
                'rejected' => \App\Models\RequestFormPivot::where('request_status', 'rejected')->count(),
            ],
            'inventory' => [
                'items'              => Item::count(),
                'transactions_today' => Transaction::whereDate('created_at', $now->toDateString())->count(),
                'stock_buckets'      => [
                    'empty' => $emptyStockCount,
                    'low'   => $lowStockCount,
                    'mid'   => $midStockCount,
                    'high'  => $highStockCount,
                ],
            ],
        ];

        return Inertia::render('Dashboard', [
            'stats' => $stats,
        ]);
    })->name('dashboard');

    Route::prefix('apps')->group(function () {
        Route::prefix('event-forms')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Forms/FormIndex');
            })->name('forms.index');

            Route::get('/create', function () {
                return Inertia::render('Forms/FormCreate');
            })->name('forms.create');

            Route::get('/scan', function () {
                return Inertia::render('Forms/FormScan');
            })->name('forms.scan');

            Route::get('/update/{event_id?}', function ($event_id = null) {
                if (!$event_id) {
                    $event_id = request()->input('event_id'); // Fallback to query parameter
                }

                if (!$event_id) {
                    $event_id = request()->query('event_id');
                }

                return Inertia::render('Forms/FormUpdate', [
                    'data' => Form::where('event_id', $event_id)->first(),
                ]);
            })->name('forms.update');
        });

        Route::prefix('access-use-requests')->group(function () {
            Route::get('/', function () {
                return Inertia::render('LabRequest/AccessUseRequestsIndex');
            })->name('accessUseRequest.index');
        });

        Route::prefix('inventory')->group(function () {
            Route::prefix('scan')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('Inventory/Scan/Scan', [
                        'fromUrl' => url()->previous(),
                    ]);
                })->name('scan.index');
            });

            Route::prefix('items')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('Inventory/Items/Items', [
                        'fromUrl' => route('dashboard'),
                    ]);
                })->name('items.index');

                Route::get('/create', function () {
                    return Inertia::render('Inventory/Items/components/presentation/CreateItemForm', [
                        'fromUrl' => url()->previous(),
                        'suppliers' => Supplier::withTrashed()->get(),
                        'categories' => Category::all(),
                    ]);
                })->name('items.create');

                Route::get('/{id}', [ItemController::class, function () {
                    return Inertia::render('Inventory/Items/components/presentation/EditItemForm', [
                        'data' => Item::find(request()->route('id')),
                        'suppliers' => Supplier::withTrashed()->get(),
                        'categories' => Category::all(),
                        'fromUrl' => url()->previous(),
                    ]);
                }])->name('items.show');
            });

            Route::prefix('transactions')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('Inventory/Transactions/Transactions', [
                        'fromUrl' => route('dashboard'),
                    ]);
                })->name('transactions.index');

                Route::get('/incoming', function () {
                    return Inertia::render('Inventory/Transactions/components/presentation/Incoming', [
                        'fromUrl' => route('transactions.index'),
                        'items' => Item::withTrashed()->get(),
                    ]);
                })->name('transactions.incoming');

                Route::get('/outgoing', function () {
                    return Inertia::render('Inventory/Transactions/components/presentation/Outgoing', [
                        'fromUrl' => url()->previous(),
                        'personnels' => Personnel::all(),
                    ]);
                })->name('transactions.outgoing');

                Route::get('/{id}', function () {
                    $transaction = Transaction::find(request()->route('id'));
                    if (!$transaction) return redirect()->route('transactions.index');

                    if($transaction->transac_type === Inventory::INCOMING->value){
                        return Inertia::render('Inventory/Transactions/components/presentation/IncomingUpdateForm', [
                            'data' => $transaction,
                            'items' => Item::withTrashed()->get(),
                            'fromUrl' => route('transactions.index'),
                        ]);
                    } else {
                        return Inertia::render('Inventory/Transactions/components/presentation/OutgoingUpdateForm', [
                            'show' =>  Transaction::selectRaw('transactions.id, personnel_id, quantity, items.name, items.brand, transactions.unit, transac_type, items.id as item_id,
                     SUM(CASE WHEN transactions.quantity > 0 THEN transactions.quantity ELSE 0 END) as total_ingoing,
                     SUM(CASE WHEN transactions.quantity < 0 THEN ABS(transactions.quantity) ELSE 0 END) as total_outgoing,
                     SUM(transactions.quantity) as remaining_quantity')
                                ->where('transactions.id', request()->route('id'))
                                ->join('items', 'transactions.item_id', '=', 'items.id')
                                ->groupBy('transactions.id', 'items.id', 'transac_type', 'items.name', 'items.brand', 'transactions.unit')
                                ->first(),
                            'fromUrl' => route('transactions.index'),
                            'personnels' => Personnel::all(),
                        ]);
                    }
                })->name('transactions.show');
            });

            Route::prefix('personnels')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('Inventory/Personnel/Personnel', [
                        'fromUrl' => route('dashboard'),
                    ]);
                })->name('personnels.index');

                Route::get('/create', function () {
                    return Inertia::render('Inventory/Personnel/components/presentation/CreatePersonnelForm', [
                        'fromUrl' => route('personnels.index'),
                    ]);
                })->name('personnels.create');

                Route::get('/{id}', [PersonnelController::class, function () {
                    return Inertia::render('Inventory/Personnel/components/presentation/EditPersonnelForm', [
                        'data' => Personnel::find(request()->route('id')),
                        'fromUrl' => route('personnels.index'),
                    ]);
                }])->name('personnels.show');
            });

            Route::prefix('suppliers')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('Inventory/Supplier/Supplier', [
                        'fromUrl' => route('dashboard'),
                    ]);
                })->name('suppliers.index');

                Route::get('/create', function () {
                    return Inertia::render('Inventory/Supplier/components/presentation/CreateSupplierForm', [
                        'fromUrl' =>  url()->previous(),
                    ]);
                })->name('suppliers.create');

                Route::get('/{id}', [SupplierController::class, function () {
                    return Inertia::render('Inventory/Supplier/components/presentation/EditSupplierForm', [
                        'data' => Supplier::find(request()->route('id')),
                        'fromUrl' => url()->previous(),
                    ]);
                }])->name('suppliers.show');
            });
        });
    });

});
