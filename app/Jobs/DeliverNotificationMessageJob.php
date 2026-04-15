<?php

namespace App\Jobs;

use App\Notifications\GroupedBccNotification;
use App\Services\Notifications\NotificationAuditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notification as BaseNotification;
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
    public string $deliveryMode;
    public array $recipientEmails;
    public array $logIds;
    public ?string $groupedToAddress;
    public ?string $groupedToName;

    public function __construct(
        public string $mode,
        public string $recipientEmail,
        public string $className,
        public array $payload,
        public string $logId,
        string $deliveryMode = 'individual',
        array $recipientEmails = [],
        array $logIds = [],
        ?string $groupedToAddress = null,
        ?string $groupedToName = null,
    ) {
        $this->deliveryMode = $deliveryMode;
        $this->recipientEmails = !empty($recipientEmails) ? array_values($recipientEmails) : [$recipientEmail];
        $this->logIds = !empty($logIds) ? array_values($logIds) : [$logId];
        $this->groupedToAddress = $groupedToAddress;
        $this->groupedToName = $groupedToName;
    }

    public function handle(NotificationAuditService $audit): void
    {
        try {
            if ($this->mode === 'notification') {
                $this->sendNotification();
            } else {
                $this->sendMailable();
            }

            $audit->markSentMany($this->resolveLogIds());
        } catch (\Throwable $exception) {
            $audit->markFailedMany($this->resolveLogIds(), $exception->getMessage());
            throw $exception;
        }
    }

    private function sendNotification(): void
    {
        $notification = new $this->className($this->payload);

        if (!$notification instanceof BaseNotification) {
            throw new \RuntimeException(sprintf('%s is not a valid notification class.', $this->className));
        }

        [$to, $bcc] = $this->resolveDeliveryRecipients();
        $notifiable = new AnonymousNotifiable();
        $notifiable->route('mail', $to);

        if ($this->deliveryMode === 'grouped' && !empty($bcc)) {
            $notification = new GroupedBccNotification($notification, $bcc);
        }

        Notification::sendNow($notifiable, $notification);
    }

    private function sendMailable(): void
    {
        [$to, $bcc] = $this->resolveDeliveryRecipients();
        $mailable = new $this->className(...$this->payload);

        $pendingMail = Mail::to($to);

        if ($this->deliveryMode === 'grouped' && !empty($bcc)) {
            $pendingMail->bcc($bcc);
        }

        $pendingMail->send($mailable);
    }

    private function resolveDeliveryRecipients(): array
    {
        $emails = $this->resolveRecipientEmails();

        if ($emails === []) {
            throw new \RuntimeException('Notification job does not have any resolved recipient email addresses.');
        }

        if ($this->deliveryMode !== 'grouped' || count($emails) === 1) {
            return [$emails[0], []];
        }

        $groupedTo = $this->groupedToAddress ?? config('notifications.grouped.to.address');
        $groupedName = $this->groupedToName ?? config('notifications.grouped.to.name');

        if (is_string($groupedTo) && trim($groupedTo) !== '') {
            $to = trim($groupedTo);

            if (is_string($groupedName) && trim($groupedName) !== '') {
                $to = [$to => trim($groupedName)];
            }

            return [$to, $emails];
        }

        return [array_shift($emails), $emails];
    }

    private function resolveRecipientEmails(): array
    {
        $emails = $this->recipientEmails;

        if ($emails === [] && $this->recipientEmail !== '') {
            $emails = [$this->recipientEmail];
        }

        return collect($emails)
            ->filter(fn ($email) => is_string($email) && trim($email) !== '')
            ->map(fn (string $email) => trim($email))
            ->values()
            ->all();
    }

    private function resolveLogIds(): array
    {
        $logIds = $this->logIds;

        if ($logIds === [] && $this->logId !== '') {
            $logIds = [$this->logId];
        }

        return collect($logIds)
            ->filter(fn ($logId) => is_string($logId) && trim($logId) !== '')
            ->map(fn (string $logId) => trim($logId))
            ->values()
            ->all();
    }
}
