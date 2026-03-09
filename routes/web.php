<?php

use App\Enums\Inventory;
use App\Enums\Role as RoleEnum;
use App\Http\Controllers\EventSubformController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\InventoryFormController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LabRequestFormController;
use App\Http\Controllers\PDFGeneratorController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\SupplierController;
use App\Models\Category;
use App\Repositories\OptionRepo;
use App\Models\Item;
use App\Models\Personnel;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use App\Http\Controllers\DashboardController;
use App\Repositories\CategoryRepo;
use App\Repositories\SupplierRepo;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Svg\Tag\Rect;

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

Route::get('/inventory/outgoing', [InventoryFormController::class, 'outgoingForm'])->name('inventory.public.outgoing.index');

Route::prefix('forms')->group(function () {
    Route::get('/event/{event?}', [FormController::class, 'formGuestView'])->name('forms.guest.index');
    Route::get('/request-to-use/{request?}', [LabRequestFormController::class, 'labReqFormGuestView'])->name('labReq.guest.index');
    Route::get('/{id}/pdf', [PDFGeneratorController::class, 'downloadPdf'])->name('forms.generate.pdf');
    //Route::get('/{id}/pdf', function(){ return view('generator.pdf.printable-request-form', [ 'form' => \App\Models\RequestFormPivot::with(['requester', 'request_form'])->findOrFail(request()->route('id')), 'forPdf' => false ]);})->name('forms.generate.pdf');
});

Route::prefix('/laboratory')->group(function () {
        Route::get('/equipments/{equipment_id?}', function ($equipment_id = null) {
            return Inertia::render('Laboratory/EquipmentShow', [
                'equipment_id' => $equipment_id,
                'logger_type' => 'laboratory',
            ]);
        })->name('laboratory.equipments.show');

        Route::get('/experiments-monitoring', function () {
            return Inertia::render('Laboratory/ExperimentsMonitoring/ExperimentsMonitoringGuest');
        })->name('laboratory.monitoring.guest');
});

Route::prefix('/ict')->group(function () {
        Route::get('/equipments/{equipment_id?}', function ($equipment_id = null) {
            return Inertia::render('Laboratory/EquipmentShow', [
                'equipment_id' => $equipment_id,
                'logger_type' => 'ict',
            ]);
        })->name('ict.equipments.show');
});

Route::prefix('rental')->group(function () {
    Route::get('/vehicle', function () {
        return Inertia::render('Rentals/VehicleRentalFormGuest', [
            'vehicleOptions' => app(OptionRepo::class)->getVehicles(),
        ]);
    })->name('rental.vehicle.guest');
    
    Route::get('/venue', function () {
        return Inertia::render('Rentals/VenueRentalFormGuest',[
            'venueOptions' => app(OptionRepo::class)->getEventHalls(),
        ]);
    })->name('rental.venue.guest');
});

Route::prefix('file-report')->group(function () {
    Route::get('/create-guest/{barcode?}', function ($barcode = null) {
        return Inertia::render('Inventory/SuppEquipReports/SuppEquipReportsCreateGuest', [
            'reportTemplates' => config('suppequipreportforms'),
            'barcode' => $barcode,
        ]);
    })->name('suppEquipReports.create.guest');
});

Route::middleware([
    'auth:sanctum',
    'verified',
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('apps')->group(function () {
        Route::prefix('manuals')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Manuals/ManualsIndex');
            })->name('manuals.index');
        });

        Route::prefix('laboratory')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Laboratory/LaboratoryDashboard');
            })->name('laboratory.dashboard');
        });

        Route::prefix('rentals')->group(function () {
            Route::get('/vehicle', function () {
                return Inertia::render('Rentals/RentalsVehicleIndex');
            })->name('rentals.vehicle.index');

            Route::get('/venue', function () {
                return Inertia::render('Rentals/RentalsVenueIndex');
            })->name('rentals.venue.index');

            Route::get('/calendar', function () {
                return Inertia::render('Rentals/CalendarModule');
            })->name('rentals.calendar.index');
        });

        Route::prefix('event-forms')->group(function () {
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
        });

        Route::prefix('certificates')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Certificates/CertificateGenerator');
            })->name('certificates.index');
        });

        Route::prefix('file-report')->group(function () {
            Route::get('/', function () {
                return Inertia::render('Inventory/SuppEquipReports/SuppEquipReportsIndex');
            })->name('suppEquipReports.index');

            Route::get('/create', function () {
                return Inertia::render('Inventory/SuppEquipReports/SuppEquipReportsCreate', [
                    'reportTemplates' => config('suppequipreportforms'),
                ]);
            })->name('suppEquipReports.create');
        });

        Route::prefix('access-use-requests')->group(function () {
            Route::get('/', function () {
                return Inertia::render('LabRequest/AccessUseRequestsIndex');
            })->name('accessUseRequest.index');
        });

        Route::prefix('inventory')->group(function () {
            Route::prefix('items')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('Inventory/Items/Items', [
                        'fromUrl' => route('dashboard'),
                    ]);
                })->name('items.index');

                Route::get('/create', function () {
                    return Inertia::render('Inventory/Items/components/CreateItemForm', [
                        'fromUrl' => url()->previous(),
                        'suppliers' => app(SupplierRepo::class)->getOptions(),
                        'categories' => app(CategoryRepo::class)->getInventoryFormCategories(),
                    ]);
                })->name('items.create');

                Route::get('/{id}', [ItemController::class, function () {
                    return Inertia::render('Inventory/Items/components/EditItemForm', [
                        'data' => Item::find(request()->route('id')),
                        'suppliers' => app(SupplierRepo::class)->getOptions(),
                        'categories' => app(CategoryRepo::class)->getInventoryFormCategories(),
                        'fromUrl' => url()->previous(),
                    ]);
                }])->name('items.show');
            });

            Route::get('/barcodes/print', function () {
                return Inertia::render('Inventory/Barcodes/BarcodePrint', [
                    'fromUrl' => route('items.index'),
                    'categories' => app(CategoryRepo::class)->getInventoryFormCategories(),
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
                        'personnels' => Personnel::selectRaw(expression: 'id, employee_id, fname, mname, lname, suffix')->whereNotIn('id', [1])->get(),
                        'stockLevel' => app(OptionRepo::class)->getStockLevels(),
                        'categories' => app(CategoryRepo::class)->getInventoryFormCategories(),
                    ]);
                })->name('transactions.outgoing');

                Route::get('/{id}', function () {
                    $transaction = Transaction::find(request()->route('id'));
                    if (!$transaction) return redirect()->route('transactions.index');

                    $attachedReports = $transaction->reports()
                        ->with([
                            'user:id,name',
                            'item:id,name,brand',
                            'transaction:id,barcode',
                        ])
                        ->orderByDesc('reported_at')
                        ->orderByDesc('created_at')
                        ->get();
                    
                    if($transaction->transac_type === Inventory::INCOMING->value){
                        return Inertia::render('Inventory/Transactions/components/IncomingUpdateForm', [
                            'data' => $transaction,
                            'items' => Item::withTrashed()->get(),
                            'fromUrl' => route('transactions.index'),
                            'storage_locations' => app(OptionRepo::class)->getStorageLocations(),
                            'personnels' => Personnel::selectRaw('id, employee_id, fname, mname, lname, suffix')->whereNotIn('id', [1])->get(),
                            'attachedReports' => $attachedReports,
                        ]);
                    } else {
                        return Inertia::render('Inventory/Transactions/components/OutgoingUpdateForm', [
                            'data' =>  Transaction::select('*')->where('transactions.id', request()->route('id'))->first(),
                            'summary' =>  Transaction::selectRaw('
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
                            'fromUrl' => route('transactions.index'),
                            'personnels' => Personnel::selectRaw('id, employee_id, fname, mname, lname, suffix')->whereNotIn('id', [1])->get(),
                            'attachedReports' => $attachedReports,
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
                    return Inertia::render('Inventory/Personnel/components/CreatePersonnelForm', [
                        'fromUrl' => route('personnels.index'),
                    ]);
                })->name('personnels.create');

                Route::get('/{id}', [PersonnelController::class, function () {
                    return Inertia::render('Inventory/Personnel/components/EditPersonnelForm', [
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
                    return Inertia::render('Inventory/Supplier/components/CreateSupplierForm', [
                        'fromUrl' =>  url()->previous(),
                    ]);
                })->name('suppliers.create');

                Route::get('/{id}', [SupplierController::class, function () {
                    return Inertia::render('Inventory/Supplier/components/EditSupplierForm', [
                        'data' => Supplier::find(request()->route('id')),
                        'fromUrl' => url()->previous(),
                    ]);
                }])->name('suppliers.show');
            });
        });

        Route::prefix('event-forms')->group(function () {
            Route::get('/{event_id}', [FormController::class, 'show'])->name('forms.show');
            Route::post('/{event_id}/requirements', [FormController::class, 'updateRequirements'])->name('forms.requirements.update');
        });

        Route::prefix('system')->group(function () {
            Route::prefix('options')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('System/Options/OptionsIndex', [
                        'options' => \App\Models\Option::all(),
                    ]);
                })->name('system.options.index');

                Route::get('/create', function () {
                    return Inertia::render('System/Options/CreateOption');
                })->name('system.options.create');

                Route::get('/{id}', function () {
                    return Inertia::render('System/Options/EditOption', [
                        'data' => \App\Models\Option::find(request()->route('id')),
                    ]);
                })->name('system.options.show');
            });

            Route::middleware(['auth', 'can:users.manage'])->prefix('users')->group(function () {
                Route::get('/', function () {
                    return Inertia::render('System/Users/UsersIndex');
                })->name('system.users.index');

                Route::get('/create', function () {
                    return Inertia::render('System/Users/CreateUser', [
                        'roleOptions' => RoleEnum::values(),
                        'permissionOptions' => config('rbac.permissions', []),
                    ]);
                })->name('system.users.create');

                Route::get('/{id}', function () {
                    return Inertia::render('System/Users/EditUser', [
                        'data' => User::query()->with('roles:id,name,label')->findOrFail(request()->route('id')),
                        'roleOptions' => RoleEnum::values(),
                        'permissionOptions' => config('rbac.permissions', []),
                    ]);
                })->name('system.users.show');
            });
        });
    });

});
