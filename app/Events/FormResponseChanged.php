<?php

namespace App\Events;

use App\Models\EventSubformResponse;
use App\Support\Broadcasting\ChannelNames;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FormResponseChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public EventSubformResponse $response,
        public string $action,
        public string $eventId,
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(ChannelNames::formsEvent($this->eventId)),
            new PrivateChannel(ChannelNames::staffDashboard()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'forms.response.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'type' => $this->broadcastAs(),
            'action' => $this->action,
            'event_id' => $this->eventId,
            'response' => [
                'id' => $this->response->id,
                'subform_type' => $this->response->subform_type,
                'status' => $this->response->status,
                'submitted_at' => optional($this->response->submitted_at)->toIso8601String(),
            ],
            'invalidate' => [
                'forms.responses',
                'forms.dashboard',
            ],
        ];
    }
}
