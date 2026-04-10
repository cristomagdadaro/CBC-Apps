<?php

namespace App\Repositories;

use App\Models\Item;
use App\Models\Form;
use App\Models\RentalVehicle;
use App\Models\RentalVenue;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Transaction;
use App\Models\RequestFormPivot;
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
}
