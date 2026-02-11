<?php

namespace App\Console\Commands;

use App\Services\Laboratory\LaboratoryLogService;
use Illuminate\Console\Command;

class UpdateOverdueLaboratoryLogs extends Command
{
    protected $signature = 'laboratory:mark-overdue';
    protected $description = 'Mark active laboratory equipment logs as overdue when end_use_at has passed.';

    public function handle(LaboratoryLogService $service): int
    {
        $count = $service->markOverdue();
        $this->info("{$count} laboratory log(s) marked as overdue.");

        return Command::SUCCESS;
    }
}
