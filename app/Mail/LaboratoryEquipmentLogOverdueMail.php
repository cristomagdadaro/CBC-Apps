<?php

namespace App\Mail;

use App\Models\LaboratoryEquipmentLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LaboratoryEquipmentLogOverdueMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public readonly ?string $equipmentUrl;

    public function __construct(
        public readonly LaboratoryEquipmentLog $log,
        public readonly string $equipmentType = 'laboratory',
    ) {
        $this->equipmentUrl = $this->resolveEquipmentUrl();
    }

    public function build(): self
    {
        $equipmentName = $this->log->equipment?->name ?: 'Equipment';
        $typeLabel = $this->equipmentType === 'ict' ? 'ICT equipment' : 'laboratory equipment';

        return $this->subject("Overdue {$typeLabel} usage: {$equipmentName}")
            ->view('emails.laboratory.equipment-log-overdue');
    }

    private function resolveEquipmentUrl(): ?string
    {
        $identifier = (string) ($this->log->equipment?->id ?? $this->log->equipment_id ?? '');

        if ($identifier === '') {
            return null;
        }

        $routeName = $this->equipmentType === 'ict'
            ? 'ict.equipments.show'
            : 'laboratory.equipments.show';

        return route($routeName, ['equipment_id' => $identifier]);
    }
}
