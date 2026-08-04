<?php

namespace App\Console\Commands;

use App\Services\LabRequest\RequestLifecycleService;
use Illuminate\Console\Command;

class SendOverdueUseRequestNotifications extends Command
{
    protected $signature = 'fes:send-overdue-reminders';

    protected $description = 'Send reminder emails for overdue released FES requests.';

    public function handle(RequestLifecycleService $lifecycleService): int
    {
        $sentCount = $lifecycleService->dispatchScheduledOverdueNotifications();

        $this->info("Queued {$sentCount} overdue FES reminder(s).");

        return self::SUCCESS;
    }
}
