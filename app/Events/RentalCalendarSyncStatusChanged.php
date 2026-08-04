<?php

namespace App\Events;

use App\Support\Broadcasting\ChannelNames;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalCalendarSyncStatusChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
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
            new PrivateChannel(ChannelNames::rentalsCalendar()),
            new PrivateChannel(ChannelNames::staffDashboard()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'rentals.calendar.sync-status';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->broadcastAs(),
            ...$this->payload,
        ];
    }
}
