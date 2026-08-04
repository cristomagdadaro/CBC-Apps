<?php

namespace App\Events;

use App\Models\LaboratoryEquipmentLocationSurvey;
use App\Models\LaboratoryEquipmentLog;
use App\Support\Broadcasting\ChannelNames;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EquipmentLogChanged implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public string $action,
        public string $equipmentType,
        public string $equipmentId,
        public ?LaboratoryEquipmentLog $log = null,
        public ?LaboratoryEquipmentLocationSurvey $locationSurvey = null,
    ) {
    }

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel($this->equipmentType === 'ict'
                ? ChannelNames::ictLogs()
                : ChannelNames::laboratoryLogs()),
            new PrivateChannel(ChannelNames::staffDashboard()),
        ];

        $employeeId = trim((string) ($this->log?->personnel?->employee_id ?? ''));

        if ($employeeId !== '') {
            $channels[] = new PrivateChannel(ChannelNames::equipmentUser($employeeId));
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'equipment.log.changed';
    }

    public function broadcastWith(): array
    {
        $log = $this->log?->loadMissing(['equipment', 'personnel']);

        return [
            'type' => $this->broadcastAs(),
            'action' => $this->action,
            'equipment_type' => $this->equipmentType,
            'equipment_id' => $this->equipmentId,
            'log' => $log ? [
                'id' => $log->id,
                'status' => $log->status,
                'personnel_id' => $log->personnel_id,
                'personnel_name' => trim(implode(' ', array_filter([
                    $log->personnel?->fname,
                    $log->personnel?->mname,
                    $log->personnel?->lname,
                    $log->personnel?->suffix,
                ]))),
                'equipment_name' => $log->equipment?->name,
                'started_at' => optional($log->started_at)?->toIso8601String(),
                'end_use_at' => optional($log->end_use_at)?->toIso8601String(),
                'actual_end_at' => optional($log->actual_end_at)?->toIso8601String(),
            ] : null,
            'location' => $this->locationSurvey ? [
                'location_code' => $this->locationSurvey->location_code,
                'location_label' => $this->locationSurvey->location_label,
                'reported_at' => optional($this->locationSurvey->reported_at)->toIso8601String(),
            ] : null,
            'invalidate' => [
                $this->equipmentType === 'ict' ? 'ict.logs' : 'laboratory.logs',
                'dashboard.laboratory',
            ],
        ];
    }
}
