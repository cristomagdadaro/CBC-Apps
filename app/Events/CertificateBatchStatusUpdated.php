<?php

namespace App\Events;

use App\Support\Broadcasting\ChannelNames;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CertificateBatchStatusUpdated implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public array $payload)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(ChannelNames::formsEvent((string) $this->payload['event_id'])),
            new PrivateChannel(ChannelNames::certificatesBatch((string) $this->payload['batch_id'])),
        ];
    }

    public function broadcastAs(): string
    {
        return 'certificates.batch.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->broadcastAs(),
            ...$this->payload,
        ];
    }
}
