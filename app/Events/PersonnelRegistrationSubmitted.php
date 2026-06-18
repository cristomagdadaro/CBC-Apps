<?php

namespace App\Events;

use App\Models\PersonnelRegistration;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PersonnelRegistrationSubmitted implements ShouldDispatchAfterCommit
{
    use Dispatchable;
    use SerializesModels;

    public array $registration;

    public function __construct(PersonnelRegistration $registration)
    {
        $this->registration = [
            'id' => $registration->id,
            'full_name' => $registration->full_name,
            'email' => $registration->email,
            'employee_id' => $registration->employee_id,
            'position' => $registration->position,
            'is_philrice_employee' => (bool) $registration->is_philrice_employee,
            'status' => $registration->status,
            'submitted_at' => optional($registration->created_at)->toIso8601String(),
        ];
    }
}
