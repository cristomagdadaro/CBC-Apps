<?php

namespace App\Events;

use App\Support\Broadcasting\ChannelNames;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ReferenceDataChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public string $domain,
        public string $action,
        public string $entityId,
    ) {
    }

    public function broadcastOn(): array
    {
        $channel = match ($this->domain) {
            'items' => ChannelNames::inventoryItems(),
            'suppliers' => ChannelNames::inventorySuppliers(),
            default => ChannelNames::inventoryPersonnels(),
        };

        return [
            new PrivateChannel($channel),
            new PrivateChannel(ChannelNames::staffDashboard()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reference-data.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->broadcastAs(),
            'domain' => $this->domain,
            'action' => $this->action,
            'entity_id' => $this->entityId,
            'invalidate' => ["inventory.{$this->domain}"],
        ];
    }
}
