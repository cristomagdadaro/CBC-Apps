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
        $this->dispatchMailMode(
            domain: $domain,
            eventKey: $eventKey,
            messageMode: 'notification',
            className: $notificationClass,
            payload: $payload,
            queueName: $this->preferences->queueFor($domain, config('notifications.queues.default', 'notifications')),
            correlationId: $correlationId,
            meta: $meta,
            notifiableType: $notifiableType,
            notifiableId: $notifiableId,
            recipients: $this->recipients->resolve($domain),
        );
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
        $this->dispatchMailMode(
            domain: $domain,
            eventKey: $eventKey,
            messageMode: 'mailable',
            className: $mailableClass,
            payload: $constructorArguments,
            queueName: $this->preferences->queueFor($domain, config('notifications.queues.mail', 'mail')),
            correlationId: $correlationId,
            meta: $meta,
            notifiableType: $notifiableType,
            notifiableId: $notifiableId,
            recipients: $recipients ?? $this->recipients->resolve($domain),
        );
    }

    private function dispatchMailMode(
        string $domain,
        string $eventKey,
        string $messageMode,
        string $className,
        array $payload,
        string $queueName,
        string $correlationId,
        array $meta,
        ?string $notifiableType,
        ?string $notifiableId,
        array $recipients,
    ): void {
        $emails = collect($recipients)
            ->filter(fn ($email) => is_string($email) && trim($email) !== '')
            ->map(fn (string $email) => strtolower(trim($email)))
            ->unique()
            ->values();

        if ($emails->isEmpty()) {
            return;
        }

        $deliveryMode = $this->shouldUseGroupedDelivery($domain, $messageMode, $emails->count())
            ? 'grouped'
            : 'individual';

        $chunks = $this->chunkRecipients($domain, $emails, $deliveryMode);
        $groupedTo = $this->preferences->groupedToFor($domain);

        foreach ($chunks as $chunk) {
            $deliveryGroupId = $deliveryMode === 'grouped' ? (string) Str::uuid() : null;

            $logs = $chunk->map(function (string $email) use ($domain, $eventKey, $meta, $notifiableId, $notifiableType, $correlationId, $deliveryMode, $deliveryGroupId) {
                return $this->audit->createQueuedLog(
                    domain: $domain,
                    eventKey: $eventKey,
                    recipientEmail: $email,
                    channel: 'mail',
                    deliveryMode: $deliveryMode,
                    notifiableType: $notifiableType,
                    notifiableId: $notifiableId,
                    payloadMeta: $meta,
                    correlationId: $correlationId,
                    deliveryGroupId: $deliveryGroupId,
                );
            });

            DeliverNotificationMessageJob::dispatch(
                mode: $messageMode,
                recipientEmail: $chunk->first(),
                className: $className,
                payload: $payload,
                logId: (string) $logs->first()->id,
                deliveryMode: $deliveryMode,
                recipientEmails: $chunk->values()->all(),
                logIds: $logs->pluck('id')->map(fn ($id) => (string) $id)->values()->all(),
                groupedToAddress: $groupedTo['address'] ?: null,
                groupedToName: $groupedTo['name'] ?: null,
            )->onQueue($queueName);
        }
    }

    private function shouldUseGroupedDelivery(string $domain, string $messageMode, int $recipientCount): bool
    {
        if ($recipientCount <= 1) {
            return false;
        }

        if ($messageMode !== 'notification' && $domain === 'certificates.delivery') {
            return false;
        }

        return $this->preferences->deliveryModeFor($domain) === 'grouped';
    }

    private function chunkRecipients(string $domain, \Illuminate\Support\Collection $emails, string $deliveryMode): \Illuminate\Support\Collection
    {
        if ($deliveryMode !== 'grouped') {
            return $emails->map(fn (string $email) => collect([$email]));
        }

        $chunkSize = $this->preferences->groupedChunkSizeFor($domain);

        return $chunkSize ? $emails->chunk($chunkSize) : collect([$emails]);
    }
}
