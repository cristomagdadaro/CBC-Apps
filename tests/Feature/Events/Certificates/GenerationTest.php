<?php

namespace Tests\Feature\Events\Certificates;

use App\Enums\Subform;
use App\Models\EventCertificateTemplate;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class GenerationTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    protected User $user;
    protected Form $form;
    protected string $eventId;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();

        $this->user = $this->createAdminUser();
        Sanctum::actingAs($this->user);

        $this->form = Form::factory()->create();
        $this->eventId = $this->form->event_id;
    }

    public function test_certificate_generation_with_saved_template_and_response_data(): void
    {
        // Create a saved template
        Storage::disk('local')->put('certificates/templates/'.$this->eventId.'/template.pptx', 'fake pptx content');
        
        EventCertificateTemplate::create([
            'event_id' => $this->eventId,
            'template_path' => 'certificates/templates/'.$this->eventId.'/template.pptx',
            'template_name' => 'template.pptx',
            'template_mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

        // Create event subform and participant data
        $subform = EventSubform::factory()->create([
            'event_id' => $this->eventId,
        ]);

        $participant = Participant::factory()->create();
        $registration = Registration::factory()->create([
            'event_subform_id' => $subform->id,
            'participant_id' => $participant->id,
        ]);

        EventSubformResponse::create([
            'form_parent_id' => $subform->id,
            'participant_id' => $registration->id,
            'subform_type' => Subform::PREREGISTRATION->value,
            'response_data' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'course' => 'Advanced Programming',
            ],
        ]);

        // Test JSON request with saved template and event data (no file upload)
        $response = $this->postJson(
            route('api.event.certificates.generate', ['event_id' => $this->eventId]),
            [
                'format' => 'pdf',
                'use_saved_template' => true,
                'use_event_data' => true,
                'name_column' => 'name',
                'email_column' => 'email',
                'subform_type' => Subform::PREREGISTRATION->value,
            ]
        );

        $response->assertStatus(202);
        $this->assertEquals('queued', $response->json('status'));
        $this->assertNotNull($response->json('data.batch_id'));
    }

    public function test_certificate_generation_validates_required_columns_with_event_data(): void
    {
        // Create a saved template
        EventCertificateTemplate::create([
            'event_id' => $this->eventId,
            'template_path' => 'certificates/templates/'.$this->eventId.'/template.pptx',
            'template_name' => 'template.pptx',
            'template_mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

        // Test: Missing email column with event data
        $response = $this->postJson(
            route('api.event.certificates.generate', ['event_id' => $this->eventId]),
            [
                'format' => 'pdf',
                'use_saved_template' => true,
                'use_event_data' => true,
                'name_column' => 'name',
                // email_column is missing
            ]
        );

        $response->assertStatus(422);
        $this->assertArrayHasKey('email_column', $response->json('errors'));
    }

    public function test_certificate_generation_validates_boolean_flags_from_json(): void
    {
        // Create a saved template
        EventCertificateTemplate::create([
            'event_id' => $this->eventId,
            'template_path' => 'certificates/templates/'.$this->eventId.'/template.pptx',
            'template_name' => 'template.pptx',
            'template_mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

        // Test with explicit boolean false values (not strings '0')
        $response = $this->postJson(
            route('api.event.certificates.generate', ['event_id' => $this->eventId]),
            [
                'format' => 'pdf',
                'use_saved_template' => false,  // Explicit boolean false, not '0'
                'use_event_data' => false,       // Explicit boolean false, not '0'
            ],
            ['Accept' => 'application/json']
        );

        // Should fail because no template file uploaded and no saved template condition met
        $response->assertStatus(422);
        $this->assertArrayHasKey('template', $response->json('errors'));
    }

    public function test_certificate_generation_requires_data_file_when_not_using_event_data(): void
    {
        // Create a data file
        $dataFile = UploadedFile::fake()->create('data.xlsx', 100, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        // Create a template file
        $templateFile = UploadedFile::fake()->create('template.pptx', 100, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');

        // Test: Missing data file when not using event data
        $response = $this->postJson(
            route('api.event.certificates.generate', ['event_id' => $this->eventId]),
            [
                'template' => $templateFile,
                'format' => 'pdf',
                'use_saved_template' => false,
                'use_event_data' => false,
                // 'data' file is missing
            ]
        );

        $response->assertStatus(422);
        $this->assertArrayHasKey('data', $response->json('errors'));
    }

    public function test_certificate_columns_endpoint_returns_available_columns(): void
    {
        // Create event subform with response data
        $subform = EventSubform::factory()->create([
            'event_id' => $this->eventId,
        ]);

        $participant = Participant::factory()->create();
        $registration = Registration::factory()->create([
            'event_subform_id' => $subform->id,
            'participant_id' => $participant->id,
        ]);

        EventSubformResponse::create([
            'form_parent_id' => $subform->id,
            'participant_id' => $registration->id,
            'subform_type' => Subform::PREREGISTRATION->value,
            'response_data' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '+1234567890',
            ],
        ]);

        // Create a saved template
        EventCertificateTemplate::create([
            'event_id' => $this->eventId,
            'template_path' => 'certificates/templates/'.$this->eventId.'/template.pptx',
            'template_name' => 'template.pptx',
            'template_mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

        // Test columns endpoint
        $response = $this->getJson(
            route('api.event.certificates.columns', ['event_id' => $this->eventId])
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('columns', $response->json('data'));
        $this->assertArrayHasKey('subform_types', $response->json('data'));

        // Verify columns include email and name
        $columns = $response->json('data.columns');
        $this->assertContains('name', $columns);
        $this->assertContains('email', $columns);
        $this->assertContains('phone', $columns);
    }
}
