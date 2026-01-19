<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\RequestFormPivot;
use App\Models\Transaction;
use App\Observers\RequestFormPivotObserver;
use App\Observers\TransactionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!class_exists(RequestFormPivot::class)) {
            $fallback = app_path('Models/RequestFormPIvot.php');
            if (is_file($fallback)) {
                require_once $fallback;
            }
        }
        if (class_exists(RequestFormPivot::class)) {
            RequestFormPivot::observe(RequestFormPivotObserver::class);
        }
        Transaction::observe(TransactionObserver::class);
    }
}
