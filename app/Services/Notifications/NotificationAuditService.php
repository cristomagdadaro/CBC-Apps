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
        ?string $notifiableType = null,
        ?string $notifiableId = null,
        array $payloadMeta = [],
        ?string $correlationId = null,
    ): NotificationLog {
        return NotificationLog::query()->create([
            'domain' => $domain,
            'event_key' => $eventKey,
            'recipient_email' => strtolower(trim($recipientEmail)),
            'channel' => $channel,
            'status' => 'queued',
            'queued_at' => now(),
            'notifiable_type' => $notifiableType,
            'notifiable_id' => $notifiableId,
            'payload_meta' => $payloadMeta,
            'correlation_id' => $correlationId ?: (string) Str::uuid(),
        ]);
    }

    public function markSent(string $logId): void
    {
        NotificationLog::query()
            ->whereKey($logId)
            ->update([
                'status' => 'sent',
                'sent_at' => now(),
                'failed_at' => null,
                'failure_reason' => null,
            ]);
    }

    public function markFailed(string $logId, string $message): void
    {
        NotificationLog::query()
            ->whereKey($logId)
            ->update([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_reason' => $message,
            ]);
    }
}
