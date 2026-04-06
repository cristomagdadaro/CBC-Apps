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

    public function __construct(
        public readonly LaboratoryEquipmentLog $log,
        public readonly string $equipmentType = 'laboratory',
    ) {
    }

    public function build(): self
    {
        $equipmentName = $this->log->equipment?->name ?: 'Equipment';
        $typeLabel = $this->equipmentType === 'ict' ? 'ICT equipment' : 'laboratory equipment';

        return $this->subject("Overdue {$typeLabel} usage: {$equipmentName}")
            ->view('emails.laboratory.equipment-log-overdue');
    }
}
