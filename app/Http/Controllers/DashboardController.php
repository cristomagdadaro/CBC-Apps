<?php

namespace App\Http\Controllers;

use App\Repositories\DashboardRepo;
use App\Repositories\TransactionRepo;

class DashboardController extends BaseController
{
    private TransactionRepo $transactionRepo;

    public function __construct(DashboardRepo $dashboardRepo, TransactionRepo $transactionRepo)
    {
        $this->service = $dashboardRepo;
        $this->transactionRepo = $transactionRepo;
    }

    public function index()
    {
        return inertia('Dashboard', [
            'stats' => $this->dashboardRepo()->getDashboardMetrics(),
            'recentTransactions' => $this->transactionRepo->getRecentTransactions(),
        ]);
    }

    private function dashboardRepo(): DashboardRepo
    {
        return $this->requireService();
    }
}
