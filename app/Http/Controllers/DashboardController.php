<?php

namespace App\Http\Controllers;

use App\Repositories\DashboardRepo;
use App\Repositories\TransactionRepo;

class DashboardController extends Controller
{
    private DashboardRepo $dashboardRepo;
    private TransactionRepo $transactionRepo;

    public function __construct(DashboardRepo $dashboardRepo, TransactionRepo $transactionRepo)
    {
        $this->transactionRepo = $transactionRepo;
        $this->dashboardRepo = $dashboardRepo;
    }

    public function index()
    {
        return inertia('Dashboard', [
            'stats' => $this->dashboardRepo->getDashboardMetrics(),
            'recentTransactions' => $this->transactionRepo->getRecentTransactions(),
        ]);
    }
}
