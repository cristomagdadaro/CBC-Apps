<?php

namespace App\Listeners;

use App\Events\CertificateBatchStatusUpdated;
use App\Models\Form;
use App\Notifications\CertificateBatchSummaryNotification;
use App\Services\Notifications\NotificationDispatchService;

class SendCertificateBatchSummaryNotification
{
    public function __construct(private readonly NotificationDispatchService $dispatch)
    {
    }

    public function handle(CertificateBatchStatusUpdated $event): void
    {
        $status = (string) ($event->payload['status'] ?? '');

        if (!in_array($status, ['completed', 'failed'], true)) {
            return;
        }

        $eventId = (string) ($event->payload['event_id'] ?? '');
        $eventTitle = Form::query()
            ->where('event_id', $eventId)
            ->value('title');

        $this->dispatch->dispatchNotification(
            domain: 'certificates.summary',
            eventKey: "certificates.batch.{$status}",
            notificationClass: CertificateBatchSummaryNotification::class,
            payload: [
                'event_title' => $eventTitle ?: 'Event',
                'status' => $status,
                'summary' => $event->payload['summary'] ?? [],
                'error' => $event->payload['error'] ?? null,
            ],
            meta: [
                'batch_id' => $event->payload['batch_id'] ?? null,
                'event_id' => $eventId,
                'status' => $status,
            ],
        );
    }
}
