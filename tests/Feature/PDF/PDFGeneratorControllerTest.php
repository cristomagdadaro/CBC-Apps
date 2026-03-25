<?php

namespace Tests\Feature\PDF;

use App\Models\RequestFormPivot;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as PDFInstance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Mockery;
use Tests\TestCase;

class PDFGeneratorControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['is_admin' => true]));
        File::deleteDirectory(public_path('generated-pdfs'));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('generated-pdfs'));
        Mockery::close();

        parent::tearDown();
    }

    public function test_prefetch_generates_cached_pdf_and_returns_urls(): void
    {
        $pivot = RequestFormPivot::factory()->create();

        $pdfMock = Mockery::mock(PDFInstance::class);
        $pdfMock->shouldReceive('output')
            ->once()
            ->andReturn('%PDF-1.4 fake-pdf');

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data) use ($pivot) {
                return $view === 'generator/pdf/printable-request-form'
                    && ($data['form']->id ?? null) === $pivot->id
                    && ($data['forPdf'] ?? false) === true;
            })
            ->andReturn($pdfMock);

        $response = $this->getJson(route('forms.generate.pdf', [
            'id' => $pivot->id,
            'template' => 'missing/template',
            'prefetch' => 1,
        ]));

        $response->assertOk()
            ->assertJsonPath('ready', true)
            ->assertJsonPath('url', route('forms.generate.pdf', ['id' => $pivot->id, 'template' => 'generator/pdf/printable-request-form']))
            ->assertJsonPath('download_url', route('forms.generate.pdf', ['id' => $pivot->id, 'template' => 'generator/pdf/printable-request-form', 'download' => 1]));

        $this->assertTrue(File::exists(public_path('generated-pdfs/generator-pdf-printable-request-form/' . $pivot->id . '.pdf')));
    }

    public function test_generate_pdf_rejects_unknown_type(): void
    {
        $response = $this->postJson(route('inventory.generate-pdf'), [
            'type' => 'unknown-type',
        ]);

        $response->assertStatus(400)
            ->assertJson(['error' => 'Unknown PDF type']);
    }

    public function test_generate_barcode_labels_returns_download_response(): void
    {
        $pdfMock = Mockery::mock(PDF::class);
        $pdfMock->shouldReceive('setPaper')
            ->once()
            ->andReturnSelf();
        $pdfMock->shouldReceive('download')
            ->once()
            ->andReturnUsing(function (string $filename) {
                return response('fake-pdf', 200, [
                    'content-disposition' => 'attachment; filename=' . $filename,
                ]);
            });

        Pdf::shouldReceive('loadView')
            ->once()
            ->withArgs(function (string $view, array $data) {
                return $view === 'generator/pdf/barcode-labels'
                    && count($data['labels'] ?? []) === 1
                    && ($data['printMode'] ?? null) === 'barcode';
            })
            ->andReturn($pdfMock);

        $response = $this->post(route('inventory.generate-pdf'), [
            'type' => 'barcode-labels',
            'printMode' => 'barcode',
            'labels' => [[
                'name' => 'Microscope',
                'brand' => 'CBC',
                'barcode' => 'CBC-01-000010',
            ]],
            'paperWidth' => 5,
            'paperHeight' => 3,
        ]);

        $response->assertOk();
        $this->assertStringContainsString('attachment;', (string) $response->headers->get('content-disposition'));
    }

    public function test_guest_cannot_access_request_pdf(): void
    {
        auth()->logout();

        $pivot = RequestFormPivot::factory()->create();

        $response = $this->get(route('forms.generate.pdf', ['id' => $pivot->id]));

        $response->assertForbidden();
    }
}
