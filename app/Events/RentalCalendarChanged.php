<?php

namespace App\Events;

use App\Support\Broadcasting\ChannelNames;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RentalCalendarChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
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
            new Channel(ChannelNames::publicRentalsCalendar()),
            new PrivateChannel(ChannelNames::rentalsCalendar()),
            new PrivateChannel($this->payload['domain'] === 'vehicle'
                ? ChannelNames::rentalsVehicles()
                : ChannelNames::rentalsVenues()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'rentals.calendar.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->broadcastAs(),
            ...$this->payload,
        ];
    }
}
