<?php

namespace App\Http\Controllers;

use App\Models\EventCertificateTemplate;
use App\Models\EventSubformResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class EventCertificateController extends Controller
{
    public function uploadTemplate(Request $request, string $event_id): JsonResponse
    {
        $validated = $request->validate([
            'template' => ['required', 'file', 'mimes:html,htm', 'max:5120'],
        ]);

        $file = $validated['template'];
        $extension = $file->getClientOriginalExtension();
        $storedPath = $file->storeAs("certificates/templates/{$event_id}", "template.{$extension}");

        $template = EventCertificateTemplate::updateOrCreate(
            ['event_id' => $event_id],
            [
                'template_path' => $storedPath,
                'template_name' => $file->getClientOriginalName(),
                'template_mime' => $file->getClientMimeType(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'data' => $template,
        ], 200);
    }

    public function generate(Request $request, string $event_id)
    {
        $template = EventCertificateTemplate::where('event_id', $event_id)->firstOrFail();
        $templateHtml = Storage::get($template->template_path);

        $responses = EventSubformResponse::query()
            ->join('event_requirements', 'event_subform_responses.form_parent_id', '=', 'event_requirements.id')
            ->where('event_requirements.event_id', $event_id)
            ->select('event_subform_responses.*')
            ->get();

        if ($responses->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No responses found for this event.',
            ], 422);
        }

        $outputDir = public_path("generated-pdfs/certificates/{$event_id}");
        if (!File::exists($outputDir)) {
            File::makeDirectory($outputDir, 0755, true);
        }

        $pdfPaths = [];
        foreach ($responses as $response) {
            $responseData = $response->response_data ?? [];
            $name = $this->extractRespondentName($responseData);

            $html = $this->mergeTemplate($templateHtml, $response, $name, $event_id, $responseData);
            $pdf = Pdf::loadHTML($html);

            $safeName = Str::slug($name ?: 'respondent');
            $filename = $safeName . '-' . $response->id . '.pdf';
            $filePath = $outputDir . DIRECTORY_SEPARATOR . $filename;
            $pdf->save($filePath);
            $pdfPaths[] = $filePath;
        }

        $zipPath = $outputDir . DIRECTORY_SEPARATOR . 'certificates.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach ($pdfPaths as $path) {
            $zip->addFile($path, basename($path));
        }

        $zip->close();

        return response()->download($zipPath);
    }

    private function extractRespondentName(array $responseData): string
    {
        return $responseData['name']
            ?? $responseData['full_name']
            ?? $responseData['organization']
            ?? $responseData['school']
            ?? $responseData['email']
            ?? 'Participant';
    }

    private function mergeTemplate(string $template, EventSubformResponse $response, string $name, string $eventId, array $responseData): string
    {
        $replacements = [
            '{{ name }}' => $name,
            '{{name}}' => $name,
            '{{ event_id }}' => $eventId,
            '{{event_id}}' => $eventId,
            '{{ email }}' => $responseData['email'] ?? '',
            '{{email}}' => $responseData['email'] ?? '',
            '{{ date }}' => now()->format('F j, Y'),
            '{{date}}' => now()->format('F j, Y'),
            '{{ form_type }}' => $response->subform_type ?? '',
            '{{form_type}}' => $response->subform_type ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
