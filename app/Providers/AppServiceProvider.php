<?php

namespace App\Providers;

use App\Models\EventSubformResponse;
use App\Models\Item;
use App\Models\Personnel;
use App\Models\RequestFormPivot;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Observers\EventSubformResponseObserver;
use App\Observers\ItemObserver;
use App\Observers\PersonnelObserver;
use App\Observers\RequestFormPivotObserver;
use App\Observers\SupplierObserver;
use App\Observers\TransactionObserver;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Symfony\Component\Process\Process;

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
        Inertia::share('appVersion', $this->buildAppVersion());
        Inertia::share('realtime', fn () => config('realtime'));

        if (class_exists(RequestFormPivot::class)) {
            RequestFormPivot::observe(RequestFormPivotObserver::class);
        }
        Transaction::observe(TransactionObserver::class);
        EventSubformResponse::observe(EventSubformResponseObserver::class);
        Item::observe(ItemObserver::class);
        Personnel::observe(PersonnelObserver::class);
        Supplier::observe(SupplierObserver::class);
        User::observe(UserObserver::class);

        // Audit logging for models with Auditable trait is automatically registered
        // via the bootAuditable() method in the Auditable trait
    }

    protected function buildAppVersion(): string
    {
        $commitCount = $this->resolveCommitCount();

        if ($commitCount === null) {
            return config('app.version', 'v1.0.0');
        }

        $major = (int) config('app.versioning.major', 1);
        $mid = intdiv($commitCount, 100);
        $minor = $commitCount % 100;

        return sprintf('V%d.%d.%02d', $major, $mid, $minor);
    }

    protected function resolveCommitCount(): ?int
    {
        static $count;

        if ($count !== null) {
            return $count;
        }

        if (!is_dir(base_path('.git'))) {
            $count = (int) config('app.versioning.fallback_commit_count', 0);
            return $count;
        }

        try {
            $process = Process::fromShellCommandline('git rev-list --count HEAD', base_path());
            $process->run();

            if ($process->isSuccessful()) {
                $count = (int) trim($process->getOutput());
                return $count;
            }
        } catch (\Throwable) {
            // Swallow and use fallback
        }

        $count = (int) config('app.versioning.fallback_commit_count', 0);

        return $count;
    }
}
