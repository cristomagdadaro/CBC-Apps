<?php

namespace App\Repositories;

use App\Models\Item;
use App\Models\Form;
use App\Models\Personnel;
use App\Models\RentalVehicle;
use App\Models\RentalVenue;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Transaction;
use App\Models\RequestFormPivot;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardRepo extends AbstractRepoService
{
    private TransactionRepo $transactionRepo;

    public function __construct(TransactionRepo $transactionRepo)
    {
        $this->transactionRepo = $transactionRepo;
    }

    public function getDashboardMetrics()
    {
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
                'active'   => Form::where('is_suspended', false)->where('is_expired', false)->count(),
                'upcoming' => Form::whereDate('date_from', '>=', $now->toDateString())->where('is_expired', false)->count(),
                'suspended'=> Form::where('is_suspended', true)->count(),
                'expired'  => Form::where('is_expired', true)->count(),
            ],
            'access_requests' => [
                'total'    => RequestFormPivot::count(),
                'pending'  => RequestFormPivot::where('request_status', 'pending')->count(),
                'approved' => RequestFormPivot::where('request_status', 'approved')->count(),
                'released' => RequestFormPivot::where('request_status', 'released')->count(),
                'returned' => RequestFormPivot::where('request_status', 'returned')->count(),
                'overdue' => RequestFormPivot::where('request_status', 'released')
                    ->whereNull('returned_at')
                    ->whereHas('request_form', function ($query) use ($now) {
                        $query->whereNotNull('date_of_use_end')
                            ->whereNotNull('time_of_use_end')
                            ->whereRaw("TIMESTAMP(date_of_use_end, time_of_use_end) < ?", [$now->toDateTimeString()]);
                    })
                    ->count(),
                'rejected' => RequestFormPivot::where('request_status', 'rejected')->count(),
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
            'vehicle_rentals' => [
                'total'     => RentalVehicle::count(),
                'pending'   => RentalVehicle::where('status', 'pending')->count(),
                'approved'  => RentalVehicle::where('status', 'approved')->count(),
                'completed' => RentalVehicle::where('status', 'completed')->count(),
                'rejected'  => RentalVehicle::where('status', 'rejected')->count(),
            ],
            'venue_rentals' => [
                'total'     => RentalVenue::count(),
                'pending'   => RentalVenue::where('status', 'pending')->count(),
                'approved'  => RentalVenue::where('status', 'approved')->count(),
                'completed' => RentalVenue::where('status', 'completed')->count(),
                'rejected'  => RentalVenue::where('status', 'rejected')->count(),
            ],
            'laboratory_equipment' => [
                'total'   => LaboratoryEquipmentLog::count(),
                'active'  => LaboratoryEquipmentLog::where('status', 'active')->count(),
                'overdue' => LaboratoryEquipmentLog::where('status', 'overdue')->count(),
                'completed' => LaboratoryEquipmentLog::where('status', 'completed')->count(),
            ],
        ];

        return $stats;
    }

    public function getRecentEquipmentLogs(int $limit = 5): Collection
    {
        return LaboratoryEquipmentLog::query()
            ->with([
                'equipment:id,name,brand,category_id',
                'personnel:id,fname,mname,lname,suffix,employee_id',
            ])
            ->orderByRaw('COALESCE(actual_end_at, started_at) desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Global KPI counts for the system pulse banner.
     */
    public function getSystemPulse(): array
    {
        return [
            'total_users'             => User::count(),
            'total_personnel'         => Personnel::count(),
            'total_items'             => Item::count(),
            'total_forms'             => Form::count(),
            'active_equipment_logs'   => LaboratoryEquipmentLog::where('status', 'active')->count(),
            'overdue_equipment_logs'  => LaboratoryEquipmentLog::where('status', 'overdue')->count(),
            'pending_vehicle_rentals' => RentalVehicle::where('status', 'pending')->count(),
            'pending_venue_rentals'   => RentalVenue::where('status', 'pending')->count(),
            'transactions_today'      => Transaction::whereDate('created_at', today())->count(),
        ];
    }

    /**
     * 7-day daily activity counts across modules for a trend chart.
     */
    public function getWeeklyActivityTrend(): array
    {
        $days = collect();

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dateStr = $date->toDateString();

            $days->push([
                'date'         => $dateStr,
                'label'        => $date->format('D'),
                'transactions' => Transaction::whereDate('created_at', $dateStr)->count(),
                'equipment'    => LaboratoryEquipmentLog::whereDate('started_at', $dateStr)->count(),
                'rentals'      => RentalVehicle::whereDate('created_at', $dateStr)->count()
                                + RentalVenue::whereDate('created_at', $dateStr)->count(),
                'events'       => Form::whereDate('created_at', $dateStr)->count(),
            ]);
        }

        return $days->toArray();
    }

    /**
     * Per-module health summary for a horizontal stacked bar visualization.
     */
    public function getModuleHealthSummary(): array
    {
        return [
            [
                'module'    => 'Events',
                'icon'      => 'calendar',
                'route'     => 'forms.index',
                'active'    => Form::where('is_suspended', false)->where('is_expired', false)->count(),
                'pending'   => 0,
                'completed' => Form::where('is_expired', true)->count(),
                'overdue'   => Form::where('is_suspended', true)->count(),
            ],
            [
                'module'    => 'FES Requests',
                'icon'      => 'shield',
                'route'     => 'accessUseRequest.index',
                'active'    => RequestFormPivot::where('request_status', 'approved')->count(),
                'pending'   => RequestFormPivot::where('request_status', 'pending')->count(),
                'completed' => RequestFormPivot::where('request_status', 'returned')->count(),
                'overdue'   => RequestFormPivot::where('request_status', 'rejected')->count(),
            ],
            [
                'module'    => 'Inventory',
                'icon'      => 'package',
                'route'     => 'items.index',
                'active'    => Transaction::whereDate('created_at', today())->where('transac_type', 'incoming')->count(),
                'pending'   => 0,
                'completed' => Transaction::whereDate('created_at', today())->where('transac_type', 'outgoing')->count(),
                'overdue'   => 0,
            ],
            [
                'module'    => 'Vehicle Rentals',
                'icon'      => 'car',
                'route'     => 'rentals.vehicle.index',
                'active'    => RentalVehicle::where('status', 'approved')->count(),
                'pending'   => RentalVehicle::where('status', 'pending')->count(),
                'completed' => RentalVehicle::where('status', 'completed')->count(),
                'overdue'   => RentalVehicle::where('status', 'rejected')->count(),
            ],
            [
                'module'    => 'Venue Rentals',
                'icon'      => 'building',
                'route'     => 'rentals.venue.index',
                'active'    => RentalVenue::where('status', 'approved')->count(),
                'pending'   => RentalVenue::where('status', 'pending')->count(),
                'completed' => RentalVenue::where('status', 'completed')->count(),
                'overdue'   => RentalVenue::where('status', 'rejected')->count(),
            ],
            [
                'module'    => 'Laboratory',
                'icon'      => 'microscope',
                'route'     => 'equipment-logger.dashboard',
                'active'    => LaboratoryEquipmentLog::where('status', 'active')->count(),
                'pending'   => 0,
                'completed' => LaboratoryEquipmentLog::where('status', 'completed')->count(),
                'overdue'   => LaboratoryEquipmentLog::where('status', 'overdue')->count(),
            ],
        ];
    }

    /**
     * Top currently active (checked-out) equipment sessions.
     */
    public function getTopActiveEquipment(int $limit = 5): Collection
    {
        return LaboratoryEquipmentLog::query()
            ->with([
                'equipment:id,name,brand,barcode,category_id',
                'personnel:id,fname,mname,lname,suffix,employee_id',
            ])
            ->whereIn('status', ['active', 'overdue'])
            ->orderBy('started_at', 'asc')
            ->limit($limit)
            ->get(['id', 'equipment_id', 'personnel_id', 'equipment_type', 'equipment_barcode', 'status', 'started_at', 'end_use_at', 'location_label']);
    }

    /**
     * Unified recent activity feed across all modules.
     */
    public function getRecentSystemActivity(int $limit = 8): array
    {
        $activities = collect();

        // Recent transactions
        Transaction::with(['item:id,name', 'personnel:id,fname,lname'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->each(function ($t) use ($activities) {
                $activities->push([
                    'type'        => 'transaction',
                    'module'      => 'Inventory',
                    'icon'        => $t->transac_type === 'incoming' ? 'arrow-down-left' : 'arrow-up-right',
                    'color'       => $t->transac_type === 'incoming' ? 'emerald' : 'rose',
                    'title'       => ($t->item->name ?? 'Item') . ' — ' . ucfirst($t->transac_type ?? 'transaction'),
                    'subtitle'    => ($t->quantity ?? 0) . ' ' . ($t->unit ?? 'pcs') . ' by ' . trim(($t->personnel->fname ?? '') . ' ' . ($t->personnel->lname ?? '')),
                    'timestamp'   => $t->created_at?->toIso8601String(),
                    'route'       => 'transactions.show',
                    'route_param' => $t->id,
                ]);
            });

        // Recent equipment logs
        LaboratoryEquipmentLog::with(['equipment:id,name', 'personnel:id,fname,lname'])
            ->orderBy('started_at', 'desc')
            ->limit($limit)
            ->get()
            ->each(function ($log) use ($activities) {
                $activities->push([
                    'type'        => 'equipment_log',
                    'module'      => 'Laboratory',
                    'icon'        => 'microscope',
                    'color'       => $log->status === 'overdue' ? 'amber' : ($log->status === 'completed' ? 'slate' : 'indigo'),
                    'title'       => ($log->equipment->name ?? 'Equipment') . ' — ' . ucfirst($log->status ?? 'log'),
                    'subtitle'    => trim(($log->personnel->fname ?? '') . ' ' . ($log->personnel->lname ?? '')),
                    'timestamp'   => ($log->started_at ?? $log->created_at)?->toIso8601String(),
                    'route'       => null,
                    'route_param' => null,
                ]);
            });

        // Recent vehicle rentals
        RentalVehicle::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->each(function ($rental) use ($activities) {
                $activities->push([
                    'type'        => 'vehicle_rental',
                    'module'      => 'Vehicle Rental',
                    'icon'        => 'car',
                    'color'       => match ($rental->status) {
                        'pending'  => 'amber',
                        'approved' => 'emerald',
                        'rejected' => 'rose',
                        default    => 'slate',
                    },
                    'title'       => ($rental->purpose ?? 'Vehicle Booking') . ' — ' . ucfirst($rental->status ?? 'booking'),
                    'subtitle'    => $rental->requester_name ?? '',
                    'timestamp'   => $rental->created_at?->toIso8601String(),
                    'route'       => null,
                    'route_param' => null,
                ]);
            });

        // Recent venue rentals
        RentalVenue::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->each(function ($rental) use ($activities) {
                $activities->push([
                    'type'        => 'venue_rental',
                    'module'      => 'Venue Rental',
                    'icon'        => 'building',
                    'color'       => match ($rental->status) {
                        'pending'  => 'amber',
                        'approved' => 'emerald',
                        'rejected' => 'rose',
                        default    => 'slate',
                    },
                    'title'       => ($rental->venue_name ?? $rental->purpose ?? 'Venue Booking') . ' — ' . ucfirst($rental->status ?? 'booking'),
                    'subtitle'    => $rental->requester_name ?? '',
                    'timestamp'   => $rental->created_at?->toIso8601String(),
                    'route'       => null,
                    'route_param' => null,
                ]);
            });

        return $activities
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values()
            ->toArray();
    }
}
