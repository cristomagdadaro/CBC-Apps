<?php

namespace App\Console\Commands;

use App\Mail\HourlyNotificationSummaryMail;
use App\Models\NotificationLog;
use App\Services\Notifications\NotificationAuditService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendBatchedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-hourly-batch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send hourly batched notifications to recipients';

    /**
     * Execute the console command.
     */
    public function handle(NotificationAuditService $auditService): int
    {
        $logs = NotificationLog::query()
            ->where('status', 'queued')
            ->where('delivery_mode', 'batch')
            ->get();

        if ($logs->isEmpty()) {
            $this->info('No batched notifications to send.');
            return self::SUCCESS;
        }

        $groupedByRecipient = $logs->groupBy('recipient_email');
        $sentCount = 0;

        foreach ($groupedByRecipient as $email => $recipientLogs) {
            try {
                $groupedByDomain = $recipientLogs->groupBy('domain')->toArray();
                
                Mail::to($email)->send(new HourlyNotificationSummaryMail($groupedByDomain));

                $auditService->markSentMany($recipientLogs->pluck('id')->toArray());
                $this->info("Sent batch email to {$email}");
                $sentCount++;
            } catch (\Exception $e) {
                $auditService->markFailedMany($recipientLogs->pluck('id')->toArray(), $e->getMessage());
                $this->error("Failed to send batch email to {$email}: " . $e->getMessage());
            }
        }

        $this->info("Successfully sent {$sentCount} hourly batched emails.");
        
        return self::SUCCESS;
    }
}
