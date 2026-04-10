<?php

namespace App\Http\Controllers;

use App\Repositories\DashboardRepo;
use App\Repositories\TransactionRepo;
use App\Services\DeploymentAccessService;
use App\Services\RbacService;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    private TransactionRepo $transactionRepo;
    private DeploymentAccessService $deploymentAccess;
    private RbacService $rbacService;

    public function __construct(
        DashboardRepo $dashboardRepo,
        TransactionRepo $transactionRepo,
        DeploymentAccessService $deploymentAccess,
        RbacService $rbacService,
    )
    {
        $this->service = $dashboardRepo;
        $this->transactionRepo = $transactionRepo;
        $this->deploymentAccess = $deploymentAccess;
        $this->rbacService = $rbacService;
    }

    public function index(Request $request)
    {
        $dashboardAccess = $this->dashboardAccess($request);
        $stats = $this->dashboardRepo()->getDashboardMetrics();

        if (! $dashboardAccess['events']) {
            $stats['events'] = ['total' => 0, 'active' => 0, 'upcoming' => 0, 'suspended' => 0, 'expired' => 0];
        }

        if (! $dashboardAccess['fes']) {
            $stats['access_requests'] = ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
        }

        if (! $dashboardAccess['inventory']) {
            $stats['inventory'] = [
                'items' => 0,
                'transactions_today' => 0,
                'stock_buckets' => ['empty' => 0, 'low' => 0, 'mid' => 0, 'high' => 0],
            ];
        }

        if (! $dashboardAccess['rentals']) {
            $stats['vehicle_rentals'] = ['total' => 0, 'pending' => 0, 'approved' => 0, 'completed' => 0, 'rejected' => 0];
            $stats['venue_rentals'] = ['total' => 0, 'pending' => 0, 'approved' => 0, 'completed' => 0, 'rejected' => 0];
        }

        if (! $dashboardAccess['laboratory']) {
            $stats['laboratory_equipment'] = ['total' => 0, 'active' => 0, 'overdue' => 0, 'completed' => 0];
        }

        return inertia('Dashboard', [
            'stats' => $stats,
            'dashboardAccess' => $dashboardAccess,
            'recentTransactions' => $dashboardAccess['inventory']
                ? $this->transactionRepo->getRecentTransactions()
                : [],
            'recentEquipmentLogs' => $dashboardAccess['laboratory']
                ? $this->dashboardRepo()->getRecentEquipmentLogs()
                : [],
        ]);
    }

    private function dashboardRepo(): DashboardRepo
    {
        return $this->requireService();
    }

    private function dashboardAccess(Request $request): array
    {
        $user = $request->user();

        return [
            'events' => $this->canAccess($request, DeploymentAccessService::MODULE_FORMS, ['event.forms.manage']),
            'fes' => $this->canAccess($request, DeploymentAccessService::MODULE_FES, ['fes.request.approve']),
            'inventory' => $this->canAccess($request, DeploymentAccessService::MODULE_INVENTORY, ['inventory.manage']),
            'rentals' => $this->canAccess($request, DeploymentAccessService::MODULE_RENTALS, [
                'rental.vehicle.manage',
                'rental.venue.manage',
                'rental.hostel.manage',
                'rental.request.approve',
            ]),
            'laboratory' => $this->canAccess($request, DeploymentAccessService::MODULE_LABORATORY_DASHBOARD, ['laboratory.logger.manage']),
            'equipment_logger' => $this->deploymentAccess->allows($request, DeploymentAccessService::MODULE_EQUIPMENT_LOGGER),
            'is_admin' => $user ? ((bool) $user->is_admin || $user->hasRole('admin')) : false,
        ];
    }

    private function canAccess(Request $request, string $moduleKey, array $permissions): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        if ($user->is_admin || $user->hasRole('admin')) {
            return $this->deploymentAccess->allows($request, $moduleKey);
        }

        if (! $this->deploymentAccess->allows($request, $moduleKey)) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($this->rbacService->hasPermission($user, $permission)) {
                return true;
            }
        }

        return false;
    }
}
