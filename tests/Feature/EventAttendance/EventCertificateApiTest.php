<?php

namespace Tests\Feature\EventAttendance;

use App\Models\EventCertificateTemplate;
use App\Models\Form;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Models\User;

class EventCertificateApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_certificate_template(): void
    {
        Storage::fake();

        $form = Form::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $file = UploadedFile::fake()->create('template.html', 10, 'text/html');

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

        $templatePath = "certificates/templates/{$form->event_id}/template.html";
        Storage::put($templatePath, '<html><body>{{ name }}</body></html>');

        EventCertificateTemplate::create([
            'event_id' => $form->event_id,
            'template_path' => $templatePath,
            'template_name' => 'template.html',
            'template_mime' => 'text/html',
        ]);

        $response = $this->postJson(route('api.event.certificates.generate', $form->event_id));

        $response->assertStatus(422)->assertJsonStructure(['status', 'message']);
    }
}
