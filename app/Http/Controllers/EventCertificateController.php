<?php

namespace App\Http\Controllers;

use App\Models\EventCertificateTemplate;
use App\Models\EventSubformResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use ZipArchive;

class EventCertificateController extends Controller
{
    public function uploadTemplate(Request $request, string $event_id): JsonResponse
    {
        $validated = $request->validate([
            'template' => ['required', 'file', 'mimes:pptx', 'max:10240'],
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
        $request->validate([
            'format' => ['nullable', 'string', 'in:pptx,pdf,png,jpg'],
        ]);

        $template = EventCertificateTemplate::where('event_id', $event_id)->firstOrFail();
        $templatePath = Storage::path($template->template_path);

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

        $format = $request->input('format', 'pdf');
        $timestamp = now()->format('YmdHis');
        $tempDir = storage_path("app/certificates/tmp/{$event_id}/{$timestamp}");
        $outputDir = storage_path("app/certificates/output/{$event_id}/{$timestamp}");
        File::makeDirectory($tempDir, 0755, true, true);
        File::makeDirectory($outputDir, 0755, true, true);

        $csvPath = $tempDir . DIRECTORY_SEPARATOR . 'responses.csv';
        $this->writeResponsesCsv($csvPath, $responses, $event_id);

        $python = config('services.certificate_generator.python');
        if (!$python) {
            $python = PHP_OS_FAMILY === 'Windows' ? 'py' : 'python3';
        }
        $scriptPath = base_path('python/Certificate-Generator/certificate_generator.py');

        if (!File::exists($templatePath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certificate template file is missing.',
                'details' => $templatePath,
            ], 500);
        }

        if (!File::exists($scriptPath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certificate generator script not found.',
                'details' => $scriptPath,
            ], 500);
        }

        try {
            $process = new Process([
                $python,
                $scriptPath,
                '--template', $templatePath,
                '--data', $csvPath,
                '--outdir', $outputDir,
                '--format', $format,
            ]);

            $process->setTimeout(120);
            $process->run();
        } catch (\Throwable $e) {
            File::deleteDirectory($tempDir);
            File::deleteDirectory($outputDir);

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to execute certificate generator.',
                'details' => $e->getMessage(),
            ], 500);
        }

        if (!$process->isSuccessful()) {
            File::deleteDirectory($tempDir);
            File::deleteDirectory($outputDir);

            return response()->json([
                'status' => 'error',
                'message' => 'Certificate generation failed.',
                'details' => $process->getErrorOutput() ?: $process->getOutput(),
            ], 500);
        }

        $zipPath = $tempDir . DIRECTORY_SEPARATOR . "certificates-{$event_id}.zip";
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (File::allFiles($outputDir) as $file) {
            if ($file->getFilename() === basename($zipPath)) {
                continue;
            }
            $zip->addFile($file->getRealPath(), $file->getFilename());
        }

        $zip->close();
        File::deleteDirectory($outputDir);

        return response()->download($zipPath)->deleteFileAfterSend(true);
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

    private function writeResponsesCsv(string $path, $responses, string $eventId): void
    {
        $handle = fopen($path, 'w');

        $extraKeys = collect($responses)
            ->flatMap(function ($response) {
                $data = $response->response_data ?? [];
                return array_keys($data);
            })
            ->unique()
            ->values()
            ->all();

        $baseHeaders = [
            'name',
            'email',
            'event_id',
            'form_type',
            'date',
        ];

        $headers = array_values(array_unique(array_merge($baseHeaders, $extraKeys)));

        fputcsv($handle, $headers);

        foreach ($responses as $response) {
            $responseData = $response->response_data ?? [];
            $name = $this->extractRespondentName($responseData);

            $row = [
                'name' => $name,
                'email' => $responseData['email'] ?? '',
                'event_id' => $eventId,
                'form_type' => $response->subform_type ?? '',
                'date' => now()->format('F j, Y'),
            ];

            foreach ($extraKeys as $key) {
                $row[$key] = $responseData[$key] ?? '';
            }

            $orderedRow = [];
            foreach ($headers as $header) {
                $orderedRow[] = $row[$header] ?? '';
            }

            fputcsv($handle, $orderedRow);
        }

        fclose($handle);
    }
}
