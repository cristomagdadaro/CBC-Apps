<?php

namespace App\Listeners;

use App\Events\PersonnelRegistrationSubmitted;
use App\Models\PersonnelRegistration;
use App\Notifications\PersonnelRegistrationSubmittedNotification;
use App\Services\Notifications\NotificationDispatchService;

class SendPersonnelRegistrationSubmittedNotification
{
    public function __construct(private readonly NotificationDispatchService $dispatch)
    {
    }

    public function handle(PersonnelRegistrationSubmitted $event): void
    {
        $registration = $event->registration;

        $this->dispatch->dispatchNotification(
            domain: 'inventory.personnel_registrations',
            eventKey: 'inventory.personnel_registration.submitted',
            notificationClass: PersonnelRegistrationSubmittedNotification::class,
            payload: [
                'registration_id' => $registration['id'],
                'full_name' => $registration['full_name'] ?: 'New personnel registrant',
                'email' => $registration['email'] ?? null,
                'employee_id' => $registration['employee_id'] ?? null,
                'position' => $registration['position'] ?? null,
                'personnel_type' => $registration['is_philrice_employee'] ? 'PhilRice personnel' : 'External personnel',
            ],
            meta: [
                'registration_id' => $registration['id'],
                'status' => $registration['status'] ?? null,
                'submitted_at' => $registration['submitted_at'] ?? null,
            ],
            notifiableType: PersonnelRegistration::class,
            notifiableId: (string) $registration['id'],
        );
    }
}
