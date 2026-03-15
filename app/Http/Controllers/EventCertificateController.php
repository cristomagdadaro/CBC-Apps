<?php

namespace App\Http\Controllers;

use App\Http\Requests\QueueCertificateGenerationRequest;
use App\Jobs\ProcessCertificateBatchJob;
use App\Models\EventCertificateTemplate;
use App\Models\Form;
use App\Models\EventSubformResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventCertificateController extends BaseController
{
    private const DEFAULT_TEMPLATE_RELATIVE_PATH = 'app/python/Certificate-Generator/multi_template.pptx';

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

    public function generate(QueueCertificateGenerationRequest $request, string $event_id): JsonResponse
    {
        $validated = $request->validated();
        $batchId = (string) Str::uuid();
        $batchDir = "tmp/certificates/{$event_id}/{$batchId}";

        try {
            $templatePath = $this->resolveTemplatePath($request, $event_id, $batchDir);
            $dataPath = $this->resolveDataPath($request, $event_id, $batchDir, $validated);
        } catch (\RuntimeException $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage(),
            ], 422);
        }

        Cache::put($this->cacheKey($batchId), [
            'event_id' => $event_id,
            'batch_id' => $batchId,
            'status' => 'queued',
            'message' => 'Certificate request queued.',
            'zip_path' => null,
            'batch_dir' => $batchDir,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ], now()->addHours(6));

        try {
            ProcessCertificateBatchJob::dispatch(
                eventId: $event_id,
                batchId: $batchId,
                templatePath: $templatePath,
                dataPath: $dataPath,
                format: $validated['format'],
                nameTemplate: $validated['name_template'] ?? null,
            )->onQueue('default');
        } catch (\Throwable $e) {
            Storage::delete([$templatePath, $dataPath]);
            Cache::forget($this->cacheKey($batchId));

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to queue certificate generation.',
                'error' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'queued',
            'message' => 'Certificate generation queued successfully.',
            'data' => [
                'batch_id' => $batchId,
            ],
        ], 202);
    }

    public function columns(string $event_id): JsonResponse
    {
        $responses = EventSubformResponse::query()
            ->whereHas('parent', function ($query) use ($event_id) {
                $query->withTrashed()->where('event_id', $event_id);
            })
            ->select(['id', 'subform_type', 'response_data', 'submitted_at'])
            ->latest()
            ->get();

        $columnSet = [];
        foreach ($responses as $response) {
            $responseData = is_array($response->response_data) ? $response->response_data : [];
            foreach (array_keys($responseData) as $key) {
                if (!is_string($key) || trim($key) === '') {
                    continue;
                }

                $trimmed = trim($key);
                if (!in_array($trimmed, $columnSet, true)) {
                    $columnSet[] = $trimmed;
                }
            }
        }

        $template = EventCertificateTemplate::query()
            ->where('event_id', $event_id)
            ->first();

        if (!$template || !$template->template_path || !Storage::exists($template->template_path)) {
            $defaultTemplatePath = $this->defaultTemplateAbsolutePath();
            if ($defaultTemplatePath && File::exists($defaultTemplatePath)) {
                $template = [
                    'template_path' => self::DEFAULT_TEMPLATE_RELATIVE_PATH,
                    'template_name' => basename(self::DEFAULT_TEMPLATE_RELATIVE_PATH),
                    'template_mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    'is_default' => true,
                ];
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'columns' => $columnSet,
                'subform_types' => $responses->pluck('subform_type')->filter()->unique()->values(),
                'recipients' => $responses->map(function (EventSubformResponse $response) {
                    return [
                        'id' => $response->id,
                        'subform_type' => $response->subform_type,
                        'submitted_at' => optional($response->submitted_at)->toIso8601String(),
                        'response_data' => is_array($response->response_data) ? $response->response_data : [],
                    ];
                })->values(),
                'template' => $template,
            ],
        ]);
    }

    public function downloadTemplate(string $event_id)
    {
        $template = EventCertificateTemplate::query()
            ->where('event_id', $event_id)
            ->first();

        if ($template && $template->template_path && Storage::exists($template->template_path)) {
            $absolutePath = Storage::path($template->template_path);
            $downloadName = $template->template_name ?: 'template.pptx';

            return response()->download($absolutePath, $downloadName, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            ]);
        }

        $defaultTemplateAbsolutePath = $this->defaultTemplateAbsolutePath();
        if ($defaultTemplateAbsolutePath && File::exists($defaultTemplateAbsolutePath)) {
            return response()->download(
                $defaultTemplateAbsolutePath,
                basename(self::DEFAULT_TEMPLATE_RELATIVE_PATH),
                ['Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation']
            );
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Template file not found.',
        ], 404);
    }

    public function status(string $event_id, string $batch_id): JsonResponse
    {
        $payload = Cache::get($this->cacheKey($batch_id));

        if (!$payload || ($payload['event_id'] ?? null) !== $event_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certificate batch not found.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $payload,
        ]);
    }

    public function download(string $event_id, string $batch_id)
    {
        $payload = Cache::get($this->cacheKey($batch_id));

        if (!$payload || ($payload['event_id'] ?? null) !== $event_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certificate batch not found.',
            ], 404);
        }

        if (($payload['status'] ?? null) !== 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'Certificate batch is not ready for download.',
            ], 422);
        }

        $zipPath = Storage::path((string) ($payload['zip_path'] ?? ''));

        if (!File::exists($zipPath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'ZIP file not found for this batch.',
            ], 404);
        }

        return response()->download($zipPath, "certificates-{$event_id}.zip")->deleteFileAfterSend(false);
    }

    private function resolveTemplatePath(Request $request, string $eventId, string $batchDir): string
    {
        if ($request->hasFile('template')) {
            $uploadedTemplate = $request->file('template');
            $batchTemplatePath = $uploadedTemplate->storeAs($batchDir, 'template.pptx');

            $storedTemplatePath = $uploadedTemplate->storeAs("certificates/templates/{$eventId}", 'template.pptx');

            EventCertificateTemplate::updateOrCreate(
                ['event_id' => $eventId],
                [
                    'template_path' => $storedTemplatePath,
                    'template_name' => $uploadedTemplate->getClientOriginalName(),
                    'template_mime' => $uploadedTemplate->getClientMimeType(),
                ]
            );

            return $batchTemplatePath;
        }

        $useSavedTemplate = $request->boolean('use_saved_template');
        if (!$useSavedTemplate) {
            throw new \RuntimeException('Template file is required unless using the saved event template.');
        }

        $template = EventCertificateTemplate::query()
            ->where('event_id', $eventId)
            ->first();

        if ($template && $template->template_path && Storage::exists($template->template_path)) {
            $batchTemplatePath = "{$batchDir}/template.pptx";
            Storage::copy($template->template_path, $batchTemplatePath);

            return $batchTemplatePath;
        }

        $defaultTemplateAbsolutePath = $this->defaultTemplateAbsolutePath();
        if ($defaultTemplateAbsolutePath && File::exists($defaultTemplateAbsolutePath)) {
            $batchTemplatePath = "{$batchDir}/template.pptx";
            Storage::put($batchTemplatePath, File::get($defaultTemplateAbsolutePath));

            return $batchTemplatePath;
        }

        throw new \RuntimeException('No saved template found and default template is missing. Please upload a template first.');
    }

    private function resolveDataPath(Request $request, string $eventId, string $batchDir, array $validated): string
    {
        if ($request->boolean('use_event_data')) {
            $recipientIds = collect($validated['recipient_ids'] ?? [])
                ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                ->map(fn ($value) => trim($value))
                ->unique()
                ->values()
                ->all();

            $rows = $this->collectResponseRows(
                eventId: $eventId,
                subformType: $validated['subform_type'] ?? null,
                recipientIds: $recipientIds,
            );

            if (count($rows) === 0) {
                throw new \RuntimeException('No event response_data rows found for this event.');
            }

            $event = Form::query()->where('event_id', $eventId)->first();

            return $this->buildCsvDataFile(
                rows: $rows,
                batchDir: $batchDir,
                emailColumn: (string) ($validated['email_column'] ?? ''),
                nameColumn: (string) ($validated['name_column'] ?? ''),
                eventTitle: (string) ($event?->title ?? ''),
                eventDate: $this->formatEventDate($event),
                dateGiven: now()->format('F j, Y'),
            );
        }

        if (!$request->hasFile('data')) {
            throw new \RuntimeException('Data file is required when not using event response_data.');
        }

        $extension = strtolower((string) $request->file('data')->getClientOriginalExtension());
        $filename = $extension === 'csv' ? 'data.csv' : 'data.xlsx';

        return $request->file('data')->storeAs($batchDir, $filename);
    }

    private function collectResponseRows(string $eventId, ?string $subformType = null, array $recipientIds = []): array
    {
        $query = EventSubformResponse::query()
            ->whereHas('parent', function ($query) use ($eventId) {
                $query->withTrashed()->where('event_id', $eventId);
            })
            ->select(['id', 'response_data']);

        if (!empty($subformType)) {
            $query->where('subform_type', $subformType);
        }

        if (!empty($recipientIds)) {
            $query->whereIn('id', $recipientIds);
        }

        return $query
            ->get()
            ->map(function (EventSubformResponse $response) {
                return is_array($response->response_data) ? $response->response_data : [];
            })
            ->filter(fn(array $row) => count($row) > 0)
            ->values()
            ->all();
    }

    private function buildCsvDataFile(
        array $rows,
        string $batchDir,
        string $emailColumn,
        string $nameColumn,
        string $eventTitle = '',
        string $eventDate = '',
        string $dateGiven = ''
    ): string
    {
        $emailColumn = trim($emailColumn);
        $nameColumn = trim($nameColumn);
        $eventTitle = trim($eventTitle);
        $eventDate = trim($eventDate);
        $dateGiven = trim($dateGiven);

        $columns = [];
        foreach ($rows as $row) {
            foreach (array_keys($row) as $key) {
                $column = is_string($key) ? trim($key) : '';
                if ($column !== '' && !in_array($column, $columns, true)) {
                    $columns[] = $column;
                }
            }
        }

        if (!in_array($emailColumn, $columns, true)) {
            throw new \RuntimeException('Selected email column was not found in response_data.');
        }

        if (!in_array($nameColumn, $columns, true)) {
            throw new \RuntimeException('Selected name column was not found in response_data.');
        }

        if (!in_array('Email', $columns, true)) {
            $columns[] = 'Email';
        }

        if (!in_array('Fullname', $columns, true)) {
            $columns[] = 'Fullname';
        }

        if (!in_array('EVENT_TITLE', $columns, true)) {
            $columns[] = 'EVENT_TITLE';
        }

        if (!in_array('EVENT_DATE', $columns, true)) {
            $columns[] = 'EVENT_DATE';
        }

        if (!in_array('DATE_GIVEN', $columns, true)) {
            $columns[] = 'DATE_GIVEN';
        }

        $relativePath = "{$batchDir}/data.csv";
        $absolutePath = Storage::path($relativePath);

        File::ensureDirectoryExists(dirname($absolutePath));

        $stream = fopen($absolutePath, 'wb');
        if ($stream === false) {
            throw new \RuntimeException('Unable to create CSV data file for event responses.');
        }

        fputcsv($stream, $columns);

        foreach ($rows as $row) {
            $record = [];
            foreach ($columns as $column) {
                if ($column === 'Email') {
                    $value = $row[$emailColumn] ?? '';
                } elseif ($column === 'Fullname') {
                    $value = $row[$nameColumn] ?? '';
                } elseif ($column === 'EVENT_TITLE') {
                    $value = $eventTitle;
                } elseif ($column === 'EVENT_DATE') {
                    $value = $eventDate;
                } elseif ($column === 'DATE_GIVEN') {
                    $value = $dateGiven;
                } else {
                    $value = $row[$column] ?? '';
                }

                if (is_array($value) || is_object($value)) {
                    $value = json_encode($value);
                }

                $record[] = (string) $value;
            }

            fputcsv($stream, $record);
        }

        fclose($stream);

        return $relativePath;
    }

    private function formatEventDate(?Form $event): string
    {
        if (!$event || !$event->date_from || !$event->date_to) {
            return '';
        }

        $from = $event->date_from->copy();
        $to = $event->date_to->copy();

        if ($from->isSameDay($to)) {
            return $from->format('F j, Y');
        }

        return $from->format('F j, Y') . ' - ' . $to->format('F j, Y');
    }

    private function cacheKey(string $batchId): string
    {
        return "certificate_batch:{$batchId}";
    }

    private function defaultTemplateAbsolutePath(): string
    {
        return base_path(self::DEFAULT_TEMPLATE_RELATIVE_PATH);
    }
}
