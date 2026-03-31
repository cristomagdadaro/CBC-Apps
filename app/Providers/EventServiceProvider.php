<?php

namespace App\Providers;

use App\Events\CertificateBatchStatusUpdated;
use App\Events\EquipmentLogChanged;
use App\Events\FormResponseChanged;
use App\Events\InventoryTransactionChanged;
use App\Listeners\SendCertificateBatchSummaryNotification;
use App\Listeners\SendEquipmentLogLifecycleNotification;
use App\Listeners\SendFormResponseNotification;
use App\Listeners\SendSupplyCheckoutNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        FormResponseChanged::class => [
            SendFormResponseNotification::class,
        ],
        InventoryTransactionChanged::class => [
            SendSupplyCheckoutNotification::class,
        ],
        EquipmentLogChanged::class => [
            SendEquipmentLogLifecycleNotification::class,
        ],
        CertificateBatchStatusUpdated::class => [
            SendCertificateBatchSummaryNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
