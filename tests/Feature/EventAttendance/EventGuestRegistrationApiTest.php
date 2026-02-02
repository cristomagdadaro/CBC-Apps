<?php

namespace Tests\Feature\EventAttendance;

use App\Models\Form;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Registration;
use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

class EventGuestRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_registration_creates_participant_and_registration(): void
    {
        $form = Form::factory()->create([
            'is_suspended' => false,
            'is_expired' => false,
            'date_from' => now()->addDay()->format('Y-m-d'),
            'date_to' => now()->addDays(2)->format('Y-m-d'),
            'time_from' => '09:00:00',
            'time_to' => '17:00:00',
        ]);

        $payload = [
            'name' => 'Guest Participant',
            'email' => 'guest@example.com',
            'phone' => '09170000002',
            'sex' => 'Female',
            'age' => 25,
            'organization' => 'DA-CBC',
            'designation' => 'Analyst',
            'is_ip' => true,
            'is_pwd' => false,
            'city_address' => 'Tagum',
            'province_address' => 'Davao del Norte',
            'country_address' => 'Philippines',
            'agreed_tc' => true,
            'attendance_type' => 'In-person',
            'event_id' => $form->event_id,
        ];

        $response = $this->postJson(route('api.form.registration.post', ['event_id' => $form->event_id]), $payload);

        $response->assertStatus(201)
            ->assertJsonStructure(['status', 'participant_hash', 'participant', 'event_id']);

        $this->assertDatabaseHas('participants', [
            'email' => 'guest@example.com',
            'name' => 'Guest Participant',
        ]);

        $this->assertDatabaseHas('registrations', [
            'event_id' => $form->event_id,
            'attendance_type' => 'In-person',
        ]);
    }

    public function test_subform_response_index_filters_by_event_id(): void
    {
        $formA = Form::factory()->create();
        $formB = Form::factory()->create();

        $requirementA = EventSubform::factory()->create([
            'event_id' => $formA->event_id,
            'form_type' => 'registration',
        ]);

        $requirementB = EventSubform::factory()->create([
            'event_id' => $formB->event_id,
            'form_type' => 'registration',
        ]);

        $participantA = Participant::factory()->create();
        $registrationA = Registration::factory()->create([
            'event_id' => $formA->event_id,
            'participant_id' => $participantA->id,
        ]);

        $participantB = Participant::factory()->create();
        $registrationB = Registration::factory()->create([
            'event_id' => $formB->event_id,
            'participant_id' => $participantB->id,
        ]);

        EventSubformResponse::factory()->create([
            'form_parent_id' => $requirementA->id,
            'participant_id' => $registrationA->id,
            'subform_type' => 'registration',
        ]);

        EventSubformResponse::factory()->create([
            'form_parent_id' => $requirementB->id,
            'participant_id' => $registrationB->id,
            'subform_type' => 'registration',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson(route('api.subform.response.index', [
            'event_id' => $formA->event_id,
            'filter_by_parent_column' => 'form_parent_id',
            'filter_by_parent_id' => $requirementA->id,
        ]));

        $response->assertOk();

        $responseData = collect($response->json('data'));
        $this->assertCount(1, $responseData);
        $this->assertEquals($requirementA->id, $responseData->first()['form_parent_id']);
    }

    public function test_subform_response_can_be_deleted(): void
    {
        $form = Form::factory()->create();
        $requirement = EventSubform::factory()->create([
            'event_id' => $form->event_id,
            'form_type' => 'registration',
        ]);

        $participant = Participant::factory()->create();
        $registration = Registration::factory()->create([
            'event_id' => $form->event_id,
            'participant_id' => $participant->id,
        ]);

        $responseModel = EventSubformResponse::factory()->create([
            'form_parent_id' => $requirement->id,
            'participant_id' => $registration->id,
            'subform_type' => 'registration',
        ]);

        Sanctum::actingAs(User::factory()->create());

        $response = $this->deleteJson(route('api.subform.response.delete', $responseModel->id));
        $response->assertOk();

        $this->assertDatabaseMissing('event_subform_responses', [
            'id' => $responseModel->id,
        ]);
    }

    public function test_subform_registration_sets_registration_event_id(): void
    {
        $form = Form::factory()->create([
            'is_suspended' => false,
            'is_expired' => false,
            'date_from' => now()->addDay()->format('Y-m-d'),
            'date_to' => now()->addDays(2)->format('Y-m-d'),
            'time_from' => '09:00:00',
            'time_to' => '17:00:00',
        ]);

        $requirement = EventSubform::factory()->create([
            'event_id' => $form->event_id,
            'form_type' => 'registration',
            'config' => ['open_from' => now()->subHour(), 'open_to' => now()->addDay()],
        ]);

        $response = $this->postJson(route('api.subform.response.store'), [
            'form_parent_id' => $requirement->id,
            'subform_type' => 'registration',
            'response_data' => [
                'name' => 'Guest 1',
                'email' => 'guest1@example.com',
                'phone' => '09170000001',
                'sex' => 'Male',
                'age' => 30,
                'organization' => 'DA-CBC',
                'designation' => 'Analyst',
                'attendance_type' => 'In-person',
                'agreed_tc' => true,
            ],
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('registrations', [
            'event_id' => $form->event_id,
        ]);
    }

    public function test_admin_view_an_event_subform_responses(): void
    {
        // Create a form with 3 subform requirements and responses
        $form = Form::factory()->create();
        $requirements = collect(config('subformtypes'))->keys()->map(function ($type) use ($form) {
            return EventSubform::factory()->create([
                'event_id' => $form->event_id,
                'form_type' => $type,
            ]);
        });
        

        foreach ($requirements as $requirement) {
            Participant::factory()->count(2)->create()->each(function ($participant) use ($requirement, $form) {
                $registration = Registration::factory()->create([
                    'event_id' => $form->event_id,
                    'participant_id' => $participant->id,
                ]);


                EventSubformResponse::factory()->create([
                    'form_parent_id' => $requirement->id,
                    'participant_id' => $registration->id,
                    'subform_type' => $requirement->form_type,
                ]);
            });
        }
        
        // Assert total responses = requirements × participants (2)
        $this->assertEquals(
            $requirements->count() * 2,
            EventSubformResponse::count()
        );

        // Assert each requirement has exactly 2 unique participant responses
        $requirements->each(function ($requirement) {
            $responses = EventSubformResponse::where('form_parent_id', $requirement->id)->get();

            $this->assertCount(2, $responses);

            $this->assertCount(
                $responses->count(),
                $responses->pluck('participant_id')->unique()
            );
        });

        // login as admin
        Sanctum::actingAs(User::factory()->create());

        $response = $this->getJson(route('api.subform.response.index', [
            'is_exact' => 'false',
            'page' => 1,
            'per_page' => 10,
            'event_id' => $form->event_id,
            'filter_by_parent_column' => 'form_parent_id',
            'filter_by_parent_id' => $requirements->first()->id,
        ]));
        $response->assertOk();

        /*
        |--------------------------------------------------------------------------
        | Assert response structure
        |--------------------------------------------------------------------------
        */
        $response->assertJsonStructure([
            'data',
            'current_page',
            'last_page',
            'total',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Assert correct number of responses returned
        |--------------------------------------------------------------------------
        | Each requirement has 2 responses
        */
        $responseData = collect($response->json('data'));

        $this->assertCount(2, $responseData);

        /*
        |--------------------------------------------------------------------------
        | Assert all responses belong to the requested parent
        |--------------------------------------------------------------------------
        */
        $responseData->each(function ($item) use ($requirements) {
            $this->assertEquals(
                $requirements->first()->id,
                $item['form_parent_id']
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Assert unique participant per response (FK + unique index)
        |--------------------------------------------------------------------------
        */
        $this->assertCount(
            $responseData->count(),
            $responseData->pluck('participant_id')->unique()
        );
    }

    public function test_guest_registration_respects_max_slots_limit(): void
    {
        // Create a form and a registration requirement with max_slots = 2
        $form = Form::factory()->create([
            'is_suspended' => false,
            'is_expired' => false,
            'date_from' => now()->addDay()->format('Y-m-d'),
            'date_to' => now()->addDays(2)->format('Y-m-d'),
            'time_from' => '09:00:00',
            'time_to' => '17:00:00',
        ]);

        EventSubform::factory()->create([
            'event_id' => $form->event_id,
            'form_type' => 'registration',
            'max_slots' => 2,
        ]);

        // First registration - should succeed
        $payload1 = [
            'name' => 'Guest 1',
            'email' => 'guest1@example.com',
            'phone' => '09170000001',
            'sex' => 'Male',
            'age' => 25,
            'organization' => 'DA-CBC',
            'designation' => 'Analyst',
            'is_ip' => false,
            'is_pwd' => false,
            'city_address' => 'Tagum',
            'province_address' => 'Davao del Norte',
            'country_address' => 'Philippines',
            'agreed_tc' => true,
            'attendance_type' => 'In-person',
            'event_id' => $form->event_id,
        ];

        $response1 = $this->postJson(route('api.form.registration.post', ['event_id' => $form->event_id]), $payload1);
        $response1->assertStatus(201);

        // Second registration - should succeed
        $payload2 = [
            'name' => 'Guest 2',
            'email' => 'guest2@example.com',
            'phone' => '09170000002',
            'sex' => 'Female',
            'age' => 30,
            'organization' => 'DA-CBC',
            'designation' => 'Officer',
            'is_ip' => false,
            'is_pwd' => false,
            'city_address' => 'Tagum',
            'province_address' => 'Davao del Norte',
            'country_address' => 'Philippines',
            'agreed_tc' => true,
            'attendance_type' => 'In-person',
            'event_id' => $form->event_id,
        ];

        $response2 = $this->postJson(route('api.form.registration.post', ['event_id' => $form->event_id]), $payload2);
        $response2->assertStatus(201);

        // Third registration - should fail (max_slots = 2 reached)
        $payload3 = [
            'name' => 'Guest 3',
            'email' => 'guest3@example.com',
            'phone' => '09170000003',
            'sex' => 'Male',
            'age' => 35,
            'organization' => 'DA-CBC',
            'designation' => 'Manager',
            'is_ip' => false,
            'is_pwd' => false,
            'city_address' => 'Tagum',
            'province_address' => 'Davao del Norte',
            'country_address' => 'Philippines',
            'agreed_tc' => true,
            'attendance_type' => 'In-person',
            'event_id' => $form->event_id,
        ];

        $response3 = $this->postJson(route('api.form.registration.post', ['event_id' => $form->event_id]), $payload3);
        $response3->assertStatus(403)
            ->assertJsonPath('errors.full.0', 'This form has reached the maximum number of participants.');
    }

    public function test_subform_response_respects_max_slots_limit(): void
    {
        // Create a form and a requirement with max_slots = 2
        $form = Form::factory()->create([
            'is_suspended' => false,
            'is_expired' => false,
            'date_from' => now()->addDay()->format('Y-m-d'),
            'date_to' => now()->addDays(2)->format('Y-m-d'),
            'time_from' => '09:00:00',
            'time_to' => '17:00:00',
        ]);

        // Create a subform requirement
        $requirement = EventSubform::factory()->create([
            'event_id' => $form->event_id,
            'form_type' => 'registration',
            'max_slots' => 2,
            'config' => ['open_from' => now()->subHour(), 'open_to' => now()->addDay()],
        ]);

        // Verify requirement was created
        $requirementCheck = EventSubform::find($requirement->id);
        $this->assertNotNull($requirementCheck, 'EventSubform should be created');
        $this->assertEquals($form->event_id, $requirementCheck->event_id);

        // Register 2 participants to reach max_slots
        $participants = Participant::factory()->count(2)->create();
        $registrations = $participants->map(function ($participant) use ($form) {
            return Registration::factory()->create([
                'event_id' => $form->event_id,
                'participant_id' => $participant->id,
            ]);
        });

        // First subform response - should succeed
        $response1 = $this->postJson(route('api.subform.response.store'), [
            'form_parent_id' => $requirement->id,
            'participant_id' => $registrations->first()->id,
            'subform_type' => 'registration',
            'response_data' => [
                'name' => 'Guest 1',
                'email' => 'guest1@example.com',
                'phone' => '09170000001',
                'sex' => 'Male',
                'age' => 30,
                'organization' => 'DA-CBC',
                'designation' => 'Analyst',
                'agreed_tc' => true,
            ],
        ]);
        $response1->assertStatus(201);

        // Second subform response - should succeed
        $response2 = $this->postJson(route('api.subform.response.store'), [
            'form_parent_id' => $requirement->id,
            'participant_id' => $registrations->last()->id,
            'subform_type' => 'registration',
            'response_data' => [
                'name' => 'Guest 2',
                'email' => 'guest2@example.com',
                'phone' => '09170000002',
                'sex' => 'Female',
                'age' => 28,
                'organization' => 'DA-CBC',
                'designation' => 'Specialist',
                'agreed_tc' => true,
            ],
        ]);
        $response2->assertStatus(201);

        // Create a 3rd participant (not yet registered)
        $participant3 = Participant::factory()->create();
        $registration3 = Registration::factory()->create([
            'event_id' => $form->event_id,
            'participant_id' => $participant3->id,
        ]);

        // Third subform response - should fail (max_slots = 2 reached)
        $response3 = $this->postJson(route('api.subform.response.store'), [
            'form_parent_id' => $requirement->id,
            'participant_id' => $registration3->id,
            'subform_type' => 'registration',
            'response_data' => [
                'name' => 'Guest 3',
                'email' => 'guest3@example.com',
                'phone' => '09170000003',
                'sex' => 'Male',
                'age' => 35,
                'organization' => 'DA-CBC',
                'designation' => 'Manager',
                'agreed_tc' => true,
            ],
        ]);
        $response3->assertStatus(403)
            ->assertJsonPath('errors.full.0', 'This form has reached the maximum number of participants.');
    }

}