<?php

namespace App\Http\Controllers;

use App\Events\CertificateBatchStatusUpdated;
use App\Http\Requests\QueueCertificateGenerationRequest;
use App\Jobs\ProcessCertificateBatchJob;
use App\Mail\GeneratedCertificateMail;
use App\Models\CertificateLog;
use App\Models\EventCertificateTemplate;
use App\Models\EventSubformResponse;
use App\Models\Form;
use App\Models\NotificationLog;
use App\Services\Notifications\NotificationDispatchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

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

        event(new CertificateBatchStatusUpdated([
            'event_id' => $event_id,
            'batch_id' => $batchId,
            'status' => 'queued',
            'message' => 'Certificate request queued.',
            'zip_path' => null,
            'summary' => null,
            'error' => null,
        ]));

        try {
            ProcessCertificateBatchJob::dispatch(
                eventId: $event_id,
                batchId: $batchId,
                templatePath: $templatePath,
                dataPath: $dataPath,
                format: $validated['format'],
                nameTemplate: $validated['name_template'] ?? null,
                nameColumn: $validated['name_column'] ?? null,
                emailColumn: $validated['email_column'] ?? null,
                eventTitle: $validated['event_title'] ?? null,
                eventDate: $validated['event_date'] ?? null,
                dateGiven: $validated['date_given'] ?? null,
                sendEmails: $request->boolean('send_emails', false),
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
        $deliveryStatusByRecipient = $this->buildRecipientDeliveryStatusMap($responses, $event_id);

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

        $event = Form::query()
            ->where('event_id', $event_id)
            ->orWhere('id', $event_id)
            ->first(['id', 'event_id', 'title', 'date_from', 'date_to']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'event' => $event ? [
                    'id' => $event->id,
                    'event_id' => $event->event_id,
                    'title' => $event->title,
                    'date_from' => $event->date_from,
                    'date_to' => $event->date_to,
                    'formatted_date' => $this->formatEventDate($event),
                ] : null,
                'columns' => $columnSet,
                'subform_types' => $responses->pluck('subform_type')->filter()->unique()->values(),
                'recipients' => $responses->map(function (EventSubformResponse $response) use ($deliveryStatusByRecipient) {
                    $deliveryStatus = $deliveryStatusByRecipient[(string) $response->id] ?? null;

                    return [
                        'id' => $response->id,
                        'subform_type' => $response->subform_type,
                        'submitted_at' => optional($response->submitted_at)->toIso8601String(),
                        'response_data' => is_array($response->response_data) ? $response->response_data : [],
                        'certificate_delivery_status' => $deliveryStatus['status'] ?? 'not_sent',
                        'certificate_delivery_sent_at' => $deliveryStatus['sent_at'] ?? null,
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

    public function previewBatch(string $event_id, string $batch_id, Request $request)
    {
        $payload = Cache::get($this->cacheKey($batch_id));

        if (!$payload || ($payload['event_id'] ?? null) !== $event_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certificate batch not found.',
            ], 404);
        }

        $batchDir = $payload['batch_dir'] ?? "tmp/certificates/{$event_id}/{$batch_id}";
        $outputDir = Storage::path("{$batchDir}/output");

        if (!File::isDirectory($outputDir)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Rendered files directory not found.',
            ], 404);
        }

        $requestedFile = $request->query('file');
        $filePath = null;

        if ($requestedFile && is_string($requestedFile)) {
            $candidate = $outputDir . DIRECTORY_SEPARATOR . basename($requestedFile);
            if (File::exists($candidate)) {
                $filePath = $candidate;
            }
        }

        if (!$filePath) {
            $files = File::allFiles($outputDir);
            $priorityFiles = collect($files)->sortByDesc(function ($file) {
                $ext = strtolower($file->getExtension());
                if ($ext === 'pdf') return 3;
                if (in_array($ext, ['png', 'jpg', 'jpeg'])) return 2;
                return 1;
            });

            $first = $priorityFiles->first();
            if ($first) {
                $filePath = $first->getRealPath();
            }
        }

        if (!$filePath || !File::exists($filePath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No rendered certificate file available for preview.',
            ], 404);
        }

        $mimeType = File::mimeType($filePath) ?: 'application/octet-stream';

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        ]);
    }

    public function sendBatchEmails(string $event_id, string $batch_id): JsonResponse
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
                'message' => 'Cannot send emails for a batch that has not finished processing.',
            ], 422);
        }

        $records = $payload['records'] ?? [];
        if (empty($records)) {
            return response()->json([
                'status' => 'error',
                'message' => 'No certificate records found for this batch.',
            ], 404);
        }

        $eventTitle = $payload['event_title'] ?? '';
        $eventDate = $payload['event_date'] ?? '';
        $notifications = app(NotificationDispatchService::class);
        $queuedCount = 0;

        foreach ($records as $record) {
            $status = strtolower((string) ($record['status'] ?? 'fail')) === 'success';
            $recipientEmail = $record['recipient_email'] ?? null;
            $recipientName = $record['recipient_name'] ?? null;
            $recipientResponseId = trim((string) ($record['recipient_response_id'] ?? ''));
            $attachmentPath = $record['attachment_path'] ?? null;
            $filename = $record['filename'] ?? ($attachmentPath ? basename((string) $attachmentPath) : null);

            if ($status && $recipientEmail && $attachmentPath && File::exists($attachmentPath)) {
                $notifications->dispatchMailable(
                    domain: 'certificates.delivery',
                    eventKey: 'certificates.recipient.delivered',
                    mailableClass: GeneratedCertificateMail::class,
                    constructorArguments: [
                        $attachmentPath,
                        $filename ?? basename($attachmentPath),
                        $event_id,
                        $recipientName,
                        $eventTitle,
                        $eventDate,
                    ],
                    meta: [
                        'event_id' => $event_id,
                        'batch_id' => $batch_id,
                        'recipient_name' => $recipientName,
                        'recipient_response_id' => $recipientResponseId !== '' ? $recipientResponseId : null,
                    ],
                    notifiableType: CertificateLog::class,
                    notifiableId: null,
                    recipients: [$recipientEmail],
                );
                $queuedCount++;
            }
        }

        $payload['emails_sent'] = true;
        $payload['emails_sent_at'] = now()->toIso8601String();
        $payload['emails_sent_count'] = $queuedCount;
        $payload['pending_emails'] = 0;
        $payload['message'] = "Sent certificates to {$queuedCount} recipient(s).";
        Cache::put($this->cacheKey($batch_id), $payload, now()->addHours(6));

        event(new CertificateBatchStatusUpdated([
            'event_id' => $event_id,
            'batch_id' => $batch_id,
            'status' => 'completed',
            'message' => $payload['message'],
            'zip_path' => $payload['zip_path'] ?? null,
            'summary' => $payload['summary'] ?? null,
            'error' => null,
            'emails_sent' => true,
            'pending_emails' => 0,
        ]));

        return response()->json([
            'status' => 'success',
            'message' => "Successfully queued certificates for {$queuedCount} recipient(s).",
            'data' => [
                'queued_count' => $queuedCount,
            ],
        ]);
    }

    public function previewSample(QueueCertificateGenerationRequest $request, string $event_id)
    {
        $validated = $request->validated();
        $sampleId = (string) Str::uuid();
        $sampleDir = "tmp/certificates/{$event_id}/sample_{$sampleId}";
        $outputRelativeDir = "{$sampleDir}/output";
        $outputAbsoluteDir = Storage::path($outputRelativeDir);
        $manifestAbsolutePath = Storage::path("{$sampleDir}/manifest.json");

        File::ensureDirectoryExists($outputAbsoluteDir);

        try {
            $templatePath = $this->resolveTemplatePath($request, $event_id, $sampleDir);

            if ($request->boolean('use_event_data')) {
                $recipientIds = collect($validated['recipient_ids'] ?? [])
                    ->filter(fn ($value) => is_string($value) && trim($value) !== '')
                    ->map(fn ($value) => trim($value))
                    ->unique()
                    ->values()
                    ->all();

                $rows = $this->collectResponseRows(
                    eventId: $event_id,
                    subformType: $validated['subform_type'] ?? null,
                    recipientIds: !empty($recipientIds) ? [reset($recipientIds)] : [],
                );

                if (count($rows) === 0) {
                    $rows = $this->collectResponseRows(
                        eventId: $event_id,
                        subformType: $validated['subform_type'] ?? null,
                    );
                }

                if (count($rows) === 0) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No response rows available for sample preview.',
                    ], 422);
                }

                $sampleRows = array_slice($rows, 0, 1);
                $event = Form::query()->where('event_id', $event_id)->first();
                $customTitle = trim((string) ($validated['event_title'] ?? ''));
                $customDate = trim((string) ($validated['event_date'] ?? ''));
                $customDateGiven = trim((string) ($validated['date_given'] ?? ''));

                $dataPath = $this->buildCsvDataFile(
                    rows: $sampleRows,
                    batchDir: $sampleDir,
                    emailColumn: (string) ($validated['email_column'] ?? ''),
                    nameColumn: (string) ($validated['name_column'] ?? ''),
                    eventId: $event_id,
                    eventTitle: $customTitle !== '' ? $customTitle : (string) ($event?->title ?? ''),
                    eventDate: $customDate !== '' ? $customDate : $this->formatEventDate($event),
                    dateGiven: $customDateGiven !== '' ? $customDateGiven : now()->format('F j, Y'),
                );
            } else {
                if (!$request->hasFile('data')) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Data file is required for sample preview.',
                    ], 422);
                }
                $extension = strtolower((string) $request->file('data')->getClientOriginalExtension());
                $filename = $extension === 'csv' ? 'data.csv' : 'data.xlsx';
                $dataPath = $request->file('data')->storeAs($sampleDir, $filename);
            }

            $scriptPath = base_path('app/python/Certificate-Generator/certificate_generator.py');
            $libreOfficePath = config('services.certificate_generator.libreoffice_path', 'soffice');

            $event = Form::query()
                ->where('event_id', $event_id)
                ->orWhere('id', $event_id)
                ->first(['event_id', 'title', 'date_from', 'date_to']);
            $eventTitle = trim((string) (($validated['event_title'] ?? '') ?: ($event?->title ?? '')));
            $resolvedEventId = trim((string) ($event?->event_id ?? $event_id));
            $resolvedDateGiven = trim((string) (($validated['date_given'] ?? '') ?: now()->format('F j, Y')));
            $resolvedEventDate = trim((string) (($validated['event_date'] ?? '') ?: ($this->formatEventDate($event) ?: $resolvedDateGiven)));

            $baseArgs = [
                $scriptPath,
                '--template', Storage::path($templatePath),
                '--data', Storage::path($dataPath),
                '--outdir', $outputAbsoluteDir,
                '--format', $validated['format'],
                '--soffice-path', $libreOfficePath,
                '--manifest', $manifestAbsolutePath,
                '--event-id', $resolvedEventId,
                '--event-title', $eventTitle !== '' ? $eventTitle : ($resolvedEventId !== '' ? "Event {$resolvedEventId}" : 'Certificate of Participation'),
                '--event-date', $resolvedEventDate,
                '--date-given', $resolvedDateGiven,
            ];

            if (!empty($validated['name_column'])) {
                $baseArgs[] = '--name-column';
                $baseArgs[] = $validated['name_column'];
            }
            if (!empty($validated['email_column'])) {
                $baseArgs[] = '--email-column';
                $baseArgs[] = $validated['email_column'];
            }
            if (!empty($validated['name_template'])) {
                $baseArgs[] = '--name-template';
                $baseArgs[] = $validated['name_template'];
            }

            $pythonPath = config('services.certificate_generator.python_path');
            if (empty($pythonPath)) {
                $pythonPath = PHP_OS_FAMILY === 'Windows'
                    ? (File::exists(base_path('.venv/Scripts/python.exe')) ? base_path('.venv/Scripts/python.exe') : 'python')
                    : (File::exists(base_path('.venv/bin/python')) ? base_path('.venv/bin/python') : 'python3');
            }

            $process = new Process(array_merge([$pythonPath], $baseArgs));
            $process->setTimeout(60);
            $process->run();

            if (!$process->isSuccessful()) {
                $err = trim($process->getErrorOutput() ?: $process->getOutput());
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sample preview generation failed: ' . ($err ?: 'Process error'),
                ], 500);
            }

            $files = File::allFiles($outputAbsoluteDir);
            $priorityFiles = collect($files)->sortByDesc(function ($file) {
                $ext = strtolower($file->getExtension());
                if ($ext === 'pdf') return 3;
                if (in_array($ext, ['png', 'jpg', 'jpeg'])) return 2;
                return 1;
            });

            $firstFile = $priorityFiles->first();
            if (!$firstFile || !File::exists($firstFile->getRealPath())) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Sample preview file was not generated.',
                ], 500);
            }

            $mimeType = File::mimeType($firstFile->getRealPath()) ?: 'application/octet-stream';

            return response()->file($firstFile->getRealPath(), [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . basename($firstFile->getRealPath()) . '"',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to generate sample preview: ' . $e->getMessage(),
            ], 500);
        }
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
            $customTitle = trim((string) ($validated['event_title'] ?? ''));
            $customDate = trim((string) ($validated['event_date'] ?? ''));
            $customDateGiven = trim((string) ($validated['date_given'] ?? ''));

            return $this->buildCsvDataFile(
                rows: $rows,
                batchDir: $batchDir,
                emailColumn: (string) ($validated['email_column'] ?? ''),
                nameColumn: (string) ($validated['name_column'] ?? ''),
                eventId: $eventId,
                eventTitle: $customTitle !== '' ? $customTitle : (string) ($event?->title ?? ''),
                eventDate: $customDate !== '' ? $customDate : $this->formatEventDate($event),
                dateGiven: $customDateGiven !== '' ? $customDateGiven : now()->format('F j, Y'),
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
                $row = is_array($response->response_data) ? $response->response_data : [];
                $row['RECIPIENT_RESPONSE_ID'] = (string) $response->id;

                return $row;
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
        string $eventId = '',
        string $eventTitle = '',
        string $eventDate = '',
        string $dateGiven = ''
    ): string
    {
        $emailColumn = trim($emailColumn);
        $nameColumn = trim($nameColumn);
        $eventId = trim($eventId);
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

        if (!in_array('EVENT_ID', $columns, true)) {
            $columns[] = 'EVENT_ID';
        }

        if (!in_array('EVENT', $columns, true)) {
            $columns[] = 'EVENT';
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
                } elseif ($column === 'EVENT_ID') {
                    $value = $eventId;
                } elseif ($column === 'EVENT') {
                    $value = $eventId ?: $eventTitle;
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
        if (!$event) {
            return '';
        }

        if ($event->date_from && $event->date_to) {
            $from = $event->date_from->copy();
            $to = $event->date_to->copy();

            if ($from->isSameDay($to)) {
                return $from->format('F j, Y');
            }

            return $from->format('F j, Y') . ' - ' . $to->format('F j, Y');
        }

        if ($event->date_from) {
            return $event->date_from->copy()->format('F j, Y');
        }

        return '';
    }

    private function buildRecipientDeliveryStatusMap(Collection $responses, string $eventId): array
    {
        $responseIds = $responses
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();

        if ($responseIds === []) {
            return [];
        }

        $responseIdLookup = array_fill_keys($responseIds, true);

        $logs = NotificationLog::query()
            ->where('domain', 'certificates.delivery')
            ->where('payload_meta->event_id', $eventId)
            ->orderByDesc('sent_at')
            ->orderByDesc('failed_at')
            ->orderByDesc('queued_at')
            ->orderByDesc('created_at')
            ->get(['status', 'sent_at', 'failed_at', 'queued_at', 'created_at', 'payload_meta']);

        $statusMap = [];

        foreach ($logs as $log) {
            $recipientResponseId = (string) data_get($log->payload_meta, 'recipient_response_id', '');

            if ($recipientResponseId === '' || !isset($responseIdLookup[$recipientResponseId])) {
                continue;
            }

            $candidateTimestamp = $log->sent_at ?? $log->failed_at ?? $log->queued_at ?? $log->created_at;
            $existingTimestamp = $statusMap[$recipientResponseId]['timestamp'] ?? null;

            if ($existingTimestamp && $candidateTimestamp && $candidateTimestamp->lessThanOrEqualTo($existingTimestamp)) {
                continue;
            }

            $statusMap[$recipientResponseId] = [
                'status' => (string) $log->status,
                'sent_at' => optional($log->sent_at)->toIso8601String(),
                'timestamp' => $candidateTimestamp,
            ];
        }

        return $statusMap;
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
