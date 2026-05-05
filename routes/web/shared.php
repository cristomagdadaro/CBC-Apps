<?php

use App\Enums\Inventory;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Transaction;
use Illuminate\Foundation\Application;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $today = Carbon::today();

    $activeEquipmentCount = function (string $equipmentType): int {
        $categoryIds = $equipmentType === 'ict' ? [4] : [7];

        return LaboratoryEquipmentLog::query()
            ->where('status', 'active')
            ->whereHas('equipment.category', function ($query) use ($equipmentType, $categoryIds) {
                $query->whereIn('categories.id', $categoryIds);

                if ($equipmentType === 'laboratory') {
                    $query->orWhere('categories.name', 'Laboratory Equipment');
                }
            })
            ->count();
    };

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'serviceMetrics' => [
            'equipment-logger' => $activeEquipmentCount('laboratory'),
            'ict-equipment-logger' => $activeEquipmentCount('ict'),
            'supplies-checkout' => Transaction::query()
                ->where('transac_type', Inventory::OUTGOING->value)
                ->whereDate('created_at', $today)
                ->count(),
        ],
    ]);
});

Route::prefix('apps')->group(function () {
    Route::get('manuals', function () {
        return Inertia::render('Manuals/ManualsIndex');
    })->name('manuals.index');
});
