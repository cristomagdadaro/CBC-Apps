<?php

namespace App\Console\Commands;

use App\Models\Personnel;
use Illuminate\Console\Command;

class DeactivateExpiredPersonnel extends Command
{
    protected $signature = 'personnel:deactivate-expired';
    protected $description = 'Set personnel status to Inactive when their expires_at date has passed';

    public function handle(): void
    {
        $activeStatus = config('system.statuses.active', 'Active');
        $suspendedStatus = config('system.statuses.suspended', 'Suspended');

        $count = Personnel::query()
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now()->startOfDay())
            ->where('status', $activeStatus)
            ->update(['status' => $suspendedStatus]);

        if ($count > 0) {
            $this->info("Deactivated {$count} expired personnel record(s).");
        } else {
            $this->info('No expired personnel found.');
        }
    }
}
