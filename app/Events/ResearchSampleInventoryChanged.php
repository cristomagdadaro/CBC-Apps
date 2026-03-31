<?php

namespace App\Events;

use App\Models\Research\ResearchSampleInventoryLog;
use App\Support\Broadcasting\ChannelNames;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ResearchSampleInventoryChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public ResearchSampleInventoryLog $log)
    {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(ChannelNames::researchSamples()),
        ];
    }

    public function broadcastAs(): string
    {
        return 'research.sample.inventory.changed';
    }

    public function broadcastWith(): array
    {
        $sample = $this->log->sample;

        return [
            'type' => $this->broadcastAs(),
            'action' => $this->log->action,
            'log' => [
                'id' => $this->log->id,
                'sample_id' => $this->log->sample_id,
                'performed_by' => $this->log->performed_by,
                'created_at' => optional($this->log->created_at)->toIso8601String(),
            ],
            'sample' => [
                'id' => $sample?->id,
                'uid' => $sample?->uid,
                'accession_name' => $sample?->accession_name,
            ],
            'invalidate' => [
                'research.samples',
            ],
        ];
    }
}
