<?php

namespace App\Services\Notifications;

use App\Jobs\DeliverNotificationMessageJob;
use Illuminate\Support\Str;

class NotificationDispatchService
{
    public function __construct(
        private readonly NotificationPreferenceService $preferences,
        private readonly RecipientResolver $recipients,
        private readonly NotificationAuditService $audit,
    ) {
    }

    public function dispatchNotification(
        string $domain,
        string $eventKey,
        string $notificationClass,
        array $payload,
        array $meta = [],
        ?string $notifiableType = null,
        ?string $notifiableId = null,
    ): void {
        if (!$this->preferences->isEnabled($domain)) {
            return;
        }

        $correlationId = (string) Str::uuid();

        foreach ($this->recipients->resolve($domain) as $email) {
            $log = $this->audit->createQueuedLog(
                domain: $domain,
                eventKey: $eventKey,
                recipientEmail: $email,
                channel: 'mail',
                notifiableType: $notifiableType,
                notifiableId: $notifiableId,
                payloadMeta: $meta,
                correlationId: $correlationId,
            );

            DeliverNotificationMessageJob::dispatch(
                mode: 'notification',
                recipientEmail: $email,
                className: $notificationClass,
                payload: $payload,
                logId: (string) $log->id,
            )->onQueue($this->preferences->queueFor($domain, config('notifications.queues.default', 'notifications')));
        }
    }

    public function dispatchMailable(
        string $domain,
        string $eventKey,
        string $mailableClass,
        array $constructorArguments,
        array $meta = [],
        ?string $notifiableType = null,
        ?string $notifiableId = null,
        ?array $recipients = null,
    ): void {
        if (!$this->preferences->isEnabled($domain)) {
            return;
        }

        $correlationId = (string) Str::uuid();
        $emails = $recipients ?? $this->recipients->resolve($domain);

        foreach ($emails as $email) {
            $log = $this->audit->createQueuedLog(
                domain: $domain,
                eventKey: $eventKey,
                recipientEmail: $email,
                channel: 'mail',
                notifiableType: $notifiableType,
                notifiableId: $notifiableId,
                payloadMeta: $meta,
                correlationId: $correlationId,
            );

            DeliverNotificationMessageJob::dispatch(
                mode: 'mailable',
                recipientEmail: $email,
                className: $mailableClass,
                payload: $constructorArguments,
                logId: (string) $log->id,
            )->onQueue($this->preferences->queueFor($domain, config('notifications.queues.mail', 'mail')));
        }
    }
}
