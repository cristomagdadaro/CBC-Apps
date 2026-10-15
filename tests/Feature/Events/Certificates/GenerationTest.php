<?php

namespace Tests\Feature\Events\Certificates;

use App\Enums\Subform;
use App\Models\EventCertificateTemplate;
use App\Models\EventSubform;
use App\Models\EventSubformResponse;
use App\Models\Form;
use App\Models\NotificationLog;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use App\Jobs\ProcessCertificateBatchJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
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

    public function test_certificate_columns_endpoint_returns_recipient_delivery_status(): void
    {
        $subform = EventSubform::factory()->create([
            'event_id' => $this->eventId,
        ]);

        $participant = Participant::factory()->create();
        $registration = Registration::factory()->create([
            'event_subform_id' => $subform->id,
            'participant_id' => $participant->id,
        ]);

        $responseRecord = EventSubformResponse::create([
            'form_parent_id' => $subform->id,
            'participant_id' => $registration->id,
            'subform_type' => Subform::PREREGISTRATION->value,
            'response_data' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ],
            'submitted_at' => now(),
        ]);

        NotificationLog::query()->create([
            'domain' => 'certificates.delivery',
            'event_key' => 'certificates.recipient.delivered',
            'recipient_email' => 'jane@example.com',
            'channel' => 'mail',
            'delivery_mode' => 'individual',
            'status' => 'sent',
            'queued_at' => now()->subMinute(),
            'sent_at' => now(),
            'payload_meta' => [
                'event_id' => $this->eventId,
                'recipient_response_id' => (string) $responseRecord->id,
                'batch_id' => 'batch-test-1',
            ],
        ]);

        $response = $this->getJson(
            route('api.event.certificates.columns', ['event_id' => $this->eventId])
        );

        $response->assertStatus(200);
        $recipient = collect($response->json('data.recipients'))
            ->firstWhere('id', $responseRecord->id);

        $this->assertNotNull($recipient);
        $this->assertSame('sent', $recipient['certificate_delivery_status']);
        $this->assertNotEmpty($recipient['certificate_delivery_sent_at']);
    }

    public function test_certificate_generation_with_external_csv_and_custom_event_details(): void
    {
        $templateFile = UploadedFile::fake()->create('template.pptx', 100, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
        $csvFile = UploadedFile::fake()->create('sample.csv', 10, 'application/vnd.ms-excel');

        $response = $this->postJson(
            route('api.event.certificates.generate', ['event_id' => $this->eventId]),
            [
                'template' => $templateFile,
                'data' => $csvFile,
                'format' => 'pdf',
                'use_saved_template' => false,
                'use_event_data' => false,
                'name_column' => 'name',
                'email_column' => 'email',
                'event_title' => 'Innovate for Impact',
                'event_date' => 'October 15, 2026',
                'date_given' => 'October 15, 2026',
                'name_template' => '{event}_{Fullname}_{date}',
            ]
        );

        $response->assertStatus(202);
        $this->assertSame('queued', $response->json('status'));
        $this->assertNotNull($response->json('data.batch_id'));
    }

    public function test_certificate_columns_endpoint_returns_event_metadata(): void
    {
        $response = $this->getJson(
            route('api.event.certificates.columns', ['event_id' => $this->eventId])
        );

        $response->assertStatus(200);
        $this->assertArrayHasKey('event', $response->json('data'));
        $this->assertSame($this->form->title, $response->json('data.event.title'));
        $this->assertNotNull($response->json('data.event.formatted_date'));
    }

    public function test_certificate_generation_with_external_excel_file(): void
    {
        $templateFile = UploadedFile::fake()->create('template.pptx', 100, 'application/vnd.openxmlformats-officedocument.presentationml.presentation');
        $xlsxFile = UploadedFile::fake()->create('participants.xlsx', 50, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $response = $this->postJson(
            route('api.event.certificates.generate', ['event_id' => $this->eventId]),
            [
                'template' => $templateFile,
                'data' => $xlsxFile,
                'format' => 'pdf',
                'use_saved_template' => false,
                'use_event_data' => false,
                'name_column' => 'Full Name',
                'email_column' => 'Email',
                'event_title' => 'Biotech Symposium 2026',
                'event_date' => 'October 15, 2026',
                'date_given' => 'October 15, 2026',
            ]
        );

        $response->assertStatus(202);
        $this->assertSame('queued', $response->json('status'));
        $this->assertNotNull($response->json('data.batch_id'));
    }

    public function test_generated_certificate_mail_renders_custom_event_title_and_date(): void
    {
        $attachmentPath = tempnam(sys_get_temp_dir(), 'cert_') . '.pdf';
        file_put_contents($attachmentPath, '%PDF-fake');

        $mail = new \App\Mail\GeneratedCertificateMail(
            attachmentPath: $attachmentPath,
            displayName: 'cert.pdf',
            eventId: 'CUSTOM_EVT_999',
            recipientName: 'Maria Santos',
            eventTitle: 'National Biotech Workshop',
            eventDate: 'October 15, 2026'
        );

        $rendered = $mail->render();
        $this->assertStringContainsString('Maria Santos', $rendered);
        $this->assertStringContainsString('National Biotech Workshop', $rendered);
        $this->assertStringContainsString('October 15, 2026', $rendered);

        @unlink($attachmentPath);
    }

    public function test_certificate_preview_batch_streams_inline_rendered_file(): void
    {
        $batchId = (string) \Illuminate\Support\Str::uuid();
        $batchDir = "tmp/certificates/{$this->eventId}/{$batchId}";
        $outputDir = Storage::path("{$batchDir}/output");
        \Illuminate\Support\Facades\File::ensureDirectoryExists($outputDir);

        $dummyPdfPath = $outputDir . DIRECTORY_SEPARATOR . 'sample_cert.pdf';
        file_put_contents($dummyPdfPath, '%PDF-1.4 test certificate preview content');

        \Illuminate\Support\Facades\Cache::put("certificate_batch:{$batchId}", [
            'status' => 'completed',
            'event_id' => $this->eventId,
            'batch_dir' => $batchDir,
            'records' => [],
        ], now()->addHour());

        $response = $this->get(
            route('api.event.certificates.preview', ['event_id' => $this->eventId, 'batch_id' => $batchId])
        );

        $response->assertStatus(200);
        $this->assertStringContainsString('inline', (string) $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('sample_cert.pdf', (string) $response->headers->get('Content-Disposition'));

        \Illuminate\Support\Facades\File::deleteDirectory(Storage::path("tmp/certificates/{$this->eventId}"));
    }

    public function test_send_batch_emails_dispatches_emails_on_demand_after_review(): void
    {
        $batchId = (string) \Illuminate\Support\Str::uuid();
        $batchDir = "tmp/certificates/{$this->eventId}/{$batchId}";
        $outputDir = Storage::path("{$batchDir}/output");
        \Illuminate\Support\Facades\File::ensureDirectoryExists($outputDir);

        $certPath = $outputDir . DIRECTORY_SEPARATOR . 'John_Doe.pdf';
        file_put_contents($certPath, '%PDF-1.4 test certificate');

        \Illuminate\Support\Facades\Cache::put("certificate_batch:{$batchId}", [
            'status' => 'completed',
            'event_id' => $this->eventId,
            'batch_dir' => $batchDir,
            'event_title' => 'Rice Research Conference',
            'event_date' => 'October 15, 2026',
            'records' => [
                [
                    'status' => 'success',
                    'recipient_name' => 'John Doe',
                    'recipient_email' => 'john.doe@example.com',
                    'recipient_response_id' => '123',
                    'attachment_path' => $certPath,
                    'filename' => 'John_Doe.pdf',
                ],
            ],
            'emails_sent' => false,
            'pending_emails' => 1,
        ], now()->addHour());

        $response = $this->postJson(
            route('api.event.certificates.send_emails', ['event_id' => $this->eventId, 'batch_id' => $batchId])
        );

        $response->assertStatus(200);
        $this->assertSame('success', $response->json('status'));
        $this->assertSame(1, $response->json('data.queued_count'));

        $cached = \Illuminate\Support\Facades\Cache::get("certificate_batch:{$batchId}");
        $this->assertTrue($cached['emails_sent']);
        $this->assertSame(0, $cached['pending_emails']);

        \Illuminate\Support\Facades\File::deleteDirectory(Storage::path("tmp/certificates/{$this->eventId}"));
    }

    public function test_certificate_generation_with_send_emails_flag_false(): void
    {
        Queue::fake();

        Storage::disk('local')->put('certificates/templates/'.$this->eventId.'/template.pptx', 'fake pptx content');

        EventCertificateTemplate::create([
            'event_id' => $this->eventId,
            'template_path' => 'certificates/templates/'.$this->eventId.'/template.pptx',
            'template_name' => 'template.pptx',
            'template_mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

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
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
            ],
        ]);

        $response = $this->postJson(
            route('api.event.certificates.generate', ['event_id' => $this->eventId]),
            [
                'format' => 'pdf',
                'use_saved_template' => true,
                'use_event_data' => true,
                'name_column' => 'name',
                'email_column' => 'email',
                'send_emails' => false,
            ]
        );

        $response->assertStatus(202);
        $this->assertSame('queued', $response->json('status'));
        $this->assertNotNull($response->json('data.batch_id'));

        Queue::assertPushed(ProcessCertificateBatchJob::class, function (ProcessCertificateBatchJob $job) {
            return $job->sendEmails === false;
        });
    }

    public function test_certificate_generation_defaults_send_emails_to_false_when_omitted(): void
    {
        Queue::fake();

        Storage::disk('local')->put('certificates/templates/'.$this->eventId.'/template.pptx', 'fake pptx content');

        EventCertificateTemplate::create([
            'event_id' => $this->eventId,
            'template_path' => 'certificates/templates/'.$this->eventId.'/template.pptx',
            'template_name' => 'template.pptx',
            'template_mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

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
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
            ],
        ]);

        $response = $this->postJson(
            route('api.event.certificates.generate', ['event_id' => $this->eventId]),
            [
                'format' => 'pdf',
                'use_saved_template' => true,
                'use_event_data' => true,
                'name_column' => 'name',
                'email_column' => 'email',
            ]
        );

        $response->assertStatus(202);
        Queue::assertPushed(ProcessCertificateBatchJob::class, function (ProcessCertificateBatchJob $job) {
            return $job->sendEmails === false;
        });
    }

    public function test_certificate_generation_with_send_emails_flag_true(): void
    {
        Queue::fake();

        Storage::disk('local')->put('certificates/templates/'.$this->eventId.'/template.pptx', 'fake pptx content');

        EventCertificateTemplate::create([
            'event_id' => $this->eventId,
            'template_path' => 'certificates/templates/'.$this->eventId.'/template.pptx',
            'template_name' => 'template.pptx',
            'template_mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

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
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
            ],
        ]);

        $response = $this->postJson(
            route('api.event.certificates.generate', ['event_id' => $this->eventId]),
            [
                'format' => 'pdf',
                'use_saved_template' => true,
                'use_event_data' => true,
                'name_column' => 'name',
                'email_column' => 'email',
                'send_emails' => true,
            ]
        );

        $response->assertStatus(202);
        Queue::assertPushed(ProcessCertificateBatchJob::class, function (ProcessCertificateBatchJob $job) {
            return $job->sendEmails === true;
        });
    }
}


