<?php

namespace Tests\Feature\Events\Certificates;

use App\Models\EventCertificateTemplate;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use App\Models\Participant;
use App\Models\Registration;
use App\Jobs\ProcessCertificateBatchJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class TemplateUploadTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected $seeder = \Database\Seeders\DatabaseSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('loc_cities')->insertOrIgnore([
            'id' => 1,
            'city' => 'Cebu City',
            'province' => 'Cebu',
            'region' => 'VII',
        ]);
    }

    public function test_upload_certificate_template(): void
    {
        Storage::fake();

        $form = Form::factory()->create();
        Sanctum::actingAs($this->createAdminUser());

        $file = UploadedFile::fake()->create(
            'template.pptx',
            10,
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        );

        $response = $this->postJson(route('api.event.certificates.template.upload', $form->event_id), [
            'template' => $file,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('event_certificate_templates', [
            'event_id' => $form->event_id,
        ]);

        $template = EventCertificateTemplate::where('event_id', $form->event_id)->first();
        $this->assertNotNull($template);
    }

    public function test_generate_certificates_queues_batch_processing(): void
    {
        Storage::fake();
        Queue::fake();

        $form = Form::factory()->create();
        Sanctum::actingAs($this->createAdminUser());

        $template = UploadedFile::fake()->create(
            'template.pptx',
            10,
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        );

        $data = UploadedFile::fake()->create(
            'data.xlsx',
            10,
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );

        $response = $this->postJson(route('api.event.certificates.generate', $form->event_id), [
            'template' => $template,
            'data' => $data,
            'format' => 'pdf',
            'name_template' => '{event}_{Fullname}_{date}',
        ]);

        $response->assertStatus(202)->assertJsonStructure(['status', 'message', 'data' => ['batch_id']]);
        Queue::assertPushed(ProcessCertificateBatchJob::class);
    }

    public function test_generate_certificates_returns_validation_error_when_files_missing(): void
    {
        Storage::fake();

        $form = Form::factory()->create();
        Sanctum::actingAs($this->createAdminUser());

        $response = $this->postJson(route('api.event.certificates.generate', $form->event_id), []);

        $response->assertStatus(422)->assertJsonValidationErrors(['template', 'data', 'format']);
    }

    public function test_generate_certificates_queues_batch_processing_using_event_response_data(): void
    {
        Storage::fake();
        Queue::fake();

        $form = Form::factory()->create();
        $subform = EventSubform::factory()->create([
            'event_id' => $form->event_id,
            'form_type' => 'registration',
        ]);

        $participant = Participant::factory()->create();
        $registration = Registration::factory()->create([
            'event_subform_id' => $subform->id,
            'participant_id' => $participant->id,
        ]);

        EventSubformResponse::factory()->create([
            'form_parent_id' => $subform->id,
            'participant_id' => $registration->id,
            'subform_type' => 'registration',
            'response_data' => [
                'name' => 'Juan Dela Cruz',
                'email' => 'juan@example.test',
                'organization' => 'CBC',
            ],
        ]);

        Sanctum::actingAs($this->createAdminUser());

        $template = UploadedFile::fake()->create(
            'template.pptx',
            10,
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        );

        $response = $this->postJson(route('api.event.certificates.generate', $form->event_id), [
            'template' => $template,
            'use_event_data' => true,
            'name_column' => 'name',
            'email_column' => 'email',
            'subform_type' => 'registration',
            'format' => 'pdf',
        ]);

        $response->assertStatus(202)->assertJsonStructure(['status', 'message', 'data' => ['batch_id']]);
        Queue::assertPushed(ProcessCertificateBatchJob::class);
    }

    public function test_generate_certificates_returns_error_when_event_data_columns_are_invalid(): void
    {
        Storage::fake();

        $form = Form::factory()->create();
        $subform = EventSubform::factory()->create([
            'event_id' => $form->event_id,
            'form_type' => 'registration',
        ]);

        $participant = Participant::factory()->create();
        $registration = Registration::factory()->create([
            'event_subform_id' => $subform->id,
            'participant_id' => $participant->id,
        ]);

        EventSubformResponse::factory()->create([
            'form_parent_id' => $subform->id,
            'participant_id' => $registration->id,
            'subform_type' => 'registration',
            'response_data' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.test',
            ],
        ]);

        Sanctum::actingAs($this->createAdminUser());

        $template = UploadedFile::fake()->create(
            'template.pptx',
            10,
            'application/vnd.openxmlformats-officedocument.presentationml.presentation'
        );

        $response = $this->postJson(route('api.event.certificates.generate', $form->event_id), [
            'template' => $template,
            'use_event_data' => true,
            'name_column' => 'full_name',
            'email_column' => 'email_address',
            'format' => 'pdf',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Selected email column was not found in response_data.');
    }
}
