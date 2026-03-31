<?php

namespace App\Listeners;

use App\Events\EquipmentLogChanged;
use App\Notifications\EquipmentLogLifecycleNotification;
use App\Services\Notifications\NotificationDispatchService;

class SendEquipmentLogLifecycleNotification
{
    public function __construct(private readonly NotificationDispatchService $dispatch)
    {
    }

    public function handle(EquipmentLogChanged $event): void
    {
        if (!in_array($event->action, ['created', 'completed', 'overdue'], true) || !$event->log) {
            return;
        }

        $log = $event->log->loadMissing(['equipment', 'personnel']);
        $personnelName = trim(implode(' ', array_filter([
            $log->personnel?->fname,
            $log->personnel?->mname,
            $log->personnel?->lname,
            $log->personnel?->suffix,
        ])));

        $domain = $event->equipmentType === 'ict' ? 'ict.logs' : 'laboratory.logs';
        $moduleLabel = $event->equipmentType === 'ict' ? 'ICT Equipment Logger' : 'Laboratory Equipment Logger';

        $this->dispatch->dispatchNotification(
            domain: $domain,
            eventKey: "equipment.log.{$event->action}",
            notificationClass: EquipmentLogLifecycleNotification::class,
            payload: [
                'equipment_name' => $log->equipment?->name ?? 'Equipment',
                'status_label' => match ($event->action) {
                    'created' => 'checked out',
                    'completed' => 'checked in',
                    'overdue' => 'marked overdue',
                    default => 'updated',
                },
                'personnel_name' => $personnelName !== '' ? $personnelName : 'Unknown user',
                'module_label' => $moduleLabel,
            ],
            meta: [
                'log_id' => $log->id,
                'equipment_id' => $event->equipmentId,
                'equipment_type' => $event->equipmentType,
                'action' => $event->action,
            ],
            notifiableType: $log::class,
            notifiableId: (string) $log->id,
        );
    }
}
