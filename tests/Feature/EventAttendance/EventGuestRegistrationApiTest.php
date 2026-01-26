<?php

namespace Tests\Feature\EventAttendance;

use App\Models\Form;
use App\Models\EventRequirement;
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
            'max_slots' => 100,
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

    public function test_admin_view_an_event_subform_responses(): void
    {
        // Create a form with 3 subform requirements and responses
        $form = Form::factory()->create();
        $requirements = collect(config('subformtypes'))->keys()->map(function ($type) use ($form) {
            return EventRequirement::factory()->create([
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
            'per_page' => '*',
            'filter_by_parent_column' => 'form_parent_id',
            'filter_by_parent_id' => $requirements->first()->id,
        ]));
        $response->dump();
        $response->assertOk();

        /*
        |--------------------------------------------------------------------------
        | Assert response structure
        |--------------------------------------------------------------------------
        */
        $response->assertJsonStructure([
            'data',
            'meta',
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
}
