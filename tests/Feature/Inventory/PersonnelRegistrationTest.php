<?php

namespace Tests\Feature\Inventory;

use App\Mail\PersonnelRegistrationVerificationMail;
use App\Models\NewBarcode;
use App\Models\Personnel;
use App\Models\PersonnelRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class PersonnelRegistrationTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    public function test_guest_can_submit_personnel_registration_and_receives_verification_mail(): void
    {
        Mail::fake();

        $response = $this->postJson(route('api.inventory.personnel-registrations.store.guest'), [
            'is_philrice_employee' => true,
            'fname' => 'Ana',
            'mname' => 'L',
            'lname' => 'Rivera',
            'suffix' => null,
            'position' => 'Research Aide',
            'phone' => '09170000000',
            'address' => 'Science City of Munoz',
            'email' => 'ANA.RIVERA@example.test',
            'employee_id' => '12-3456',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.email', 'ana.rivera@example.test')
            ->assertJsonPath('data.status', PersonnelRegistration::STATUS_PENDING);

        $this->assertDatabaseHas('personnel_registrations', [
            'fname' => 'Ana',
            'email' => 'ana.rivera@example.test',
            'employee_id' => '12-3456',
            'status' => PersonnelRegistration::STATUS_PENDING,
        ]);

        Mail::assertQueued(PersonnelRegistrationVerificationMail::class, function (PersonnelRegistrationVerificationMail $mail) {
            return $mail->hasTo('ana.rivera@example.test');
        });
    }

    public function test_guest_cannot_submit_registration_for_existing_personnel_name(): void
    {
        Personnel::query()->create([
            'fname' => 'Ana',
            'lname' => 'Rivera',
            'position' => 'Research Aide',
            'email' => 'existing.ana@example.test',
            'employee_id' => '12-3998',
        ]);

        $this->postJson(route('api.inventory.personnel-registrations.store.guest'), [
            'is_philrice_employee' => true,
            'fname' => ' Ana ',
            'mname' => null,
            'lname' => 'Rivera',
            'suffix' => null,
            'position' => 'Research Aide',
            'phone' => '09170000000',
            'address' => 'Science City of Munoz',
            'email' => 'ana.new@example.test',
            'employee_id' => '12-3555',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['fname', 'lname']);

        $this->assertDatabaseMissing('personnel_registrations', [
            'email' => 'ana.new@example.test',
        ]);
    }

    public function test_guest_cannot_submit_duplicate_pending_registration_for_same_personnel_name(): void
    {
        PersonnelRegistration::query()->create([
            'is_philrice_employee' => true,
            'fname' => 'Ben',
            'lname' => 'Santos',
            'position' => 'Technician',
            'email' => 'ben.santos@example.test',
            'employee_id' => '12-3000',
            'status' => PersonnelRegistration::STATUS_PENDING,
        ]);

        $this->postJson(route('api.inventory.personnel-registrations.store.guest'), [
            'is_philrice_employee' => true,
            'fname' => 'Ben',
            'mname' => null,
            'lname' => 'Santos',
            'suffix' => null,
            'position' => 'Technician',
            'phone' => '09170000001',
            'address' => 'Science City of Munoz',
            'email' => 'ben.santos.duplicate@example.test',
            'employee_id' => '12-3005',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);

        $this->assertDatabaseMissing('personnel_registrations', [
            'email' => 'ben.santos.duplicate@example.test',
        ]);
    }

    public function test_signed_verification_route_marks_registration_email_verified(): void
    {
        $registration = PersonnelRegistration::query()->create([
            'is_philrice_employee' => true,
            'fname' => 'Ben',
            'lname' => 'Santos',
            'position' => 'Technician',
            'email' => 'ben.santos@example.test',
            'employee_id' => '12-3000',
            'status' => PersonnelRegistration::STATUS_PENDING,
        ]);

        $url = URL::temporarySignedRoute(
            'personnel.registration.verify',
            now()->addDay(),
            ['registration' => $registration->id],
        );

        $this->get($url)->assertOk();

        $this->assertNotNull($registration->fresh()->email_verified_at);
    }

    public function test_admin_cannot_approve_registration_before_email_verification(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $registration = PersonnelRegistration::query()->create([
            'is_philrice_employee' => true,
            'fname' => 'Cara',
            'lname' => 'Dela Cruz',
            'position' => 'Researcher',
            'email' => 'cara@example.test',
            'employee_id' => '12-3001',
            'status' => PersonnelRegistration::STATUS_PENDING,
        ]);

        $this->putJson(route('api.inventory.personnel-registrations.update-status', $registration->id), [
            'status' => PersonnelRegistration::STATUS_APPROVED,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);

        $this->assertDatabaseMissing('personnels', [
            'email' => 'cara@example.test',
        ]);
    }

    public function test_admin_approval_creates_verified_personnel_record(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $verifiedAt = now()->subMinute();
        $registration = PersonnelRegistration::query()->create([
            'is_philrice_employee' => true,
            'fname' => 'Dina',
            'lname' => 'Flores',
            'position' => 'Laboratory Analyst',
            'phone' => '09171111111',
            'address' => 'CBC',
            'email' => 'dina@example.test',
            'employee_id' => '12-3002',
            'status' => PersonnelRegistration::STATUS_PENDING,
            'email_verified_at' => $verifiedAt,
        ]);

        $this->putJson(route('api.inventory.personnel-registrations.update-status', $registration->id), [
            'status' => PersonnelRegistration::STATUS_APPROVED,
        ])->assertOk()
            ->assertJsonPath('data.status', PersonnelRegistration::STATUS_APPROVED);

        $this->assertDatabaseHas('personnels', [
            'fname' => 'Dina',
            'lname' => 'Flores',
            'email' => 'dina@example.test',
            'employee_id' => '12-3002',
        ]);

        $this->assertNotNull(Personnel::query()->where('email', 'dina@example.test')->first()?->email_verified_at);
        $this->assertNotNull($registration->fresh()->personnel_id);
    }

    public function test_admin_approval_for_non_philrice_registration_generates_cbc_id(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $year = now()->format('y');
        NewBarcode::query()->create([
            'room' => 'personnel_external_id',
            'barcode' => "CBC-{$year}-018",
            'name' => 'For OJT/Thesis/Outsider ID',
        ]);

        $registration = PersonnelRegistration::query()->create([
            'is_philrice_employee' => false,
            'fname' => 'Erwin',
            'lname' => 'Ocampo',
            'position' => 'OJT Student',
            'email' => 'erwin@example.test',
            'status' => PersonnelRegistration::STATUS_PENDING,
            'email_verified_at' => now(),
        ]);

        $this->putJson(route('api.inventory.personnel-registrations.update-status', $registration->id), [
            'status' => PersonnelRegistration::STATUS_APPROVED,
        ])->assertOk();

        $this->assertDatabaseHas('personnels', [
            'email' => 'erwin@example.test',
            'employee_id' => "CBC-{$year}-019",
        ]);
    }

    public function test_admin_can_reject_pending_registration_with_remarks(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $registration = PersonnelRegistration::query()->create([
            'is_philrice_employee' => true,
            'fname' => 'Faith',
            'lname' => 'Garcia',
            'position' => 'Visitor',
            'email' => 'faith@example.test',
            'employee_id' => '12-3999',
            'status' => PersonnelRegistration::STATUS_PENDING,
        ]);

        $this->putJson(route('api.inventory.personnel-registrations.update-status', $registration->id), [
            'status' => PersonnelRegistration::STATUS_REJECTED,
            'rejection_remarks' => 'Duplicate submission.',
        ])->assertOk()
            ->assertJsonPath('data.status', PersonnelRegistration::STATUS_REJECTED);

        $this->assertDatabaseHas('personnel_registrations', [
            'id' => $registration->id,
            'status' => PersonnelRegistration::STATUS_REJECTED,
            'rejection_remarks' => 'Duplicate submission.',
        ]);
    }
}
