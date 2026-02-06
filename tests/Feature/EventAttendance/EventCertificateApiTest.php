<?php

namespace Tests\Feature\EventAttendance;

use App\Models\EventCertificateTemplate;
use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;

class EventCertificateApiTest extends TestCase
{
    use RefreshDatabase;

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
        Sanctum::actingAs(User::factory()->create());

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

    public function test_generate_certificates_returns_error_when_no_responses(): void
    {
        Storage::fake();

        $form = Form::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $templatePath = "certificates/templates/{$form->event_id}/template.pptx";
        Storage::put($templatePath, 'pptx-binary');

        EventCertificateTemplate::create([
            'event_id' => $form->event_id,
            'template_path' => $templatePath,
            'template_name' => 'template.pptx',
            'template_mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        ]);

        $response = $this->postJson(route('api.event.certificates.generate', $form->event_id));

        $response->assertStatus(422)->assertJsonStructure(['status', 'message']);
    }
}
