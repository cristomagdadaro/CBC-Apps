<?php

namespace Tests\Feature\Notifications;

use App\Events\PersonnelRegistrationSubmitted;
use App\Listeners\SendPersonnelRegistrationSubmittedNotification;
use App\Models\PersonnelRegistration;
use App\Notifications\PersonnelRegistrationSubmittedNotification;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PersonnelRegistrationSubmittedNotificationListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_dispatches_notification_to_personnel_registration_domain(): void
    {
        $registration = PersonnelRegistration::query()->create([
            'is_philrice_employee' => true,
            'fname' => 'Lara',
            'mname' => 'Mae',
            'lname' => 'Santos',
            'position' => 'Laboratory Analyst',
            'email' => 'lara.santos@example.test',
            'employee_id' => '12-4567',
            'status' => PersonnelRegistration::STATUS_PENDING,
            'registration_type' => PersonnelRegistration::TYPE_PHILRICE_EMPLOYEE,
        ]);

        $dispatch = Mockery::mock(NotificationDispatchService::class);
        $listener = new SendPersonnelRegistrationSubmittedNotification($dispatch);

        $dispatch->shouldReceive('dispatchNotification')
            ->once()
            ->withArgs(function (
                string $domain,
                string $eventKey,
                string $notificationClass,
                array $payload,
                array $meta,
                ?string $notifiableType,
                ?string $notifiableId
            ) use ($registration) {
                return $domain === 'inventory.personnel_registrations'
                    && $eventKey === 'inventory.personnel_registration.submitted'
                    && $notificationClass === PersonnelRegistrationSubmittedNotification::class
                    && $payload['full_name'] === 'Lara Mae Santos'
                    && $payload['email'] === 'lara.santos@example.test'
                    && $payload['personnel_type'] === 'PhilRice personnel'
                    && $meta['registration_id'] === $registration->id
                    && $notifiableType === PersonnelRegistration::class
                    && $notifiableId === (string) $registration->id;
            });

        $listener->handle(new PersonnelRegistrationSubmitted($registration));

        $this->addToAssertionCount(1);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }
}
