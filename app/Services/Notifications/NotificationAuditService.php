<?php

namespace App\Services\Notifications;

use App\Models\NotificationLog;
use Illuminate\Support\Str;

class NotificationAuditService
{
    public function createQueuedLog(
        string $domain,
        string $eventKey,
        string $recipientEmail,
        string $channel = 'mail',
        string $deliveryMode = 'individual',
        ?string $notifiableType = null,
        ?string $notifiableId = null,
        array $payloadMeta = [],
        ?string $correlationId = null,
        ?string $deliveryGroupId = null,
    ): NotificationLog {
        return NotificationLog::query()->create([
            'domain' => $domain,
            'event_key' => $eventKey,
            'recipient_email' => strtolower(trim($recipientEmail)),
            'channel' => $channel,
            'delivery_mode' => $deliveryMode,
            'status' => 'queued',
            'queued_at' => now(),
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'payload_meta' => $payloadMeta,
            'correlation_id' => $correlationId ?: (string) Str::uuid(),
            'delivery_group_id' => $deliveryGroupId,
        ]);
    }

    public function markSent(string $logId): void
    {
        $this->markSentMany([$logId]);
    }

    public function markSentMany(array $logIds): void
    {
        NotificationLog::query()
            ->whereIn('id', array_values(array_filter($logIds)))
            ->update([
                'status' => 'sent',
                'sent_at' => now(),
                'failed_at' => null,
                'failure_reason' => null,
            ]);
    }

    public function markFailed(string $logId, string $message): void
    {
        $this->markFailedMany([$logId], $message);
    }

    public function markFailedMany(array $logIds, string $message): void
    {
        NotificationLog::query()
            ->whereIn('id', array_values(array_filter($logIds)))
            ->update([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $message,
            ]);
    }
}
