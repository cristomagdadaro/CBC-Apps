<?php

namespace App\Jobs;

use App\Services\Notifications\NotificationAuditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class DeliverNotificationMessageJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $mode,
        public string $recipientEmail,
        public string $className,
        public array $payload,
        public string $logId,
    ) {
    }

    public function handle(NotificationAuditService $audit): void
    {
        try {
            if ($this->mode === 'notification') {
                $notification = new $this->className($this->payload);
                $notifiable = new AnonymousNotifiable();
                $notifiable->route('mail', $this->recipientEmail);
                Notification::sendNow($notifiable, $notification);
            } else {
                $mailable = new $this->className(...$this->payload);
                Mail::to($this->recipientEmail)->send($mailable);
            }

            $audit->markSent($this->logId);
        } catch (\Throwable $exception) {
            $audit->markFailed($this->logId, $exception->getMessage());
            throw $exception;
        }
    }
}
