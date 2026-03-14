<?php

namespace App\Jobs;

use App\Mail\GeneratedCertificateMail;
use App\Models\CertificateLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class ProcessCertificateBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(
        public string $eventId,
        public string $batchId,
        public string $templatePath,
        public string $dataPath,
        public string $format,
        public ?string $nameTemplate = null
    ) {
    }

    public function handle(): void
    {
        $cacheKey = $this->cacheKey();
        $batchRelativeDir = "tmp/certificates/{$this->eventId}/{$this->batchId}";
        $batchAbsoluteDir = Storage::path($batchRelativeDir);
        $outputRelativeDir = "{$batchRelativeDir}/output";
        $outputAbsoluteDir = Storage::path($outputRelativeDir);
        $manifestRelativePath = "{$batchRelativeDir}/manifest.json";
        $manifestAbsolutePath = Storage::path($manifestRelativePath);

        File::ensureDirectoryExists($outputAbsoluteDir);

        Cache::put($cacheKey, [
            'event_id' => $this->eventId,
            'batch_id' => $this->batchId,
            'status' => 'processing',
            'message' => 'Processing on server...',
            'zip_path' => null,
            'batch_dir' => $batchRelativeDir,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ], now()->addHours(6));

        $libreOfficePath = config('services.certificate_generator.libreoffice_path', 'soffice');
        $scriptPath = base_path('app/python/Certificate-Generator/certificate_generator.py');

        $baseArgs = [
            $scriptPath,
            '--template', Storage::path($this->templatePath),
            '--data', Storage::path($this->dataPath),
            '--outdir', $outputAbsoluteDir,
            '--format', $this->format,
            '--soffice-path', $libreOfficePath,
            '--manifest', $manifestAbsolutePath,
        ];

        if (!empty($this->nameTemplate)) {
            $baseArgs[] = '--name-template';
            $baseArgs[] = $this->nameTemplate;
        }

        try {
            $this->runGeneratorProcess($baseArgs);
        } catch (ProcessFailedException $exception) {
            $message = trim($exception->getProcess()->getErrorOutput() ?: $exception->getProcess()->getOutput()) ?: $exception->getMessage();
            $this->recordFailure($message);

            Cache::put($cacheKey, [
                'event_id' => $this->eventId,
                'batch_id' => $this->batchId,
                'status' => 'failed',
                'message' => 'Certificate generation failed.',
                'error' => $message,
                'zip_path' => null,
                'batch_dir' => $batchRelativeDir,
                'updated_at' => now()->toIso8601String(),
            ], now()->addHours(6));

            return;
        }

        $records = $this->readManifest($manifestAbsolutePath);
        $stats = $this->persistLogsAndQueueEmails($records);

        $zipRelativePath = "{$batchRelativeDir}/certificates-{$this->batchId}.zip";
        $zipAbsolutePath = Storage::path($zipRelativePath);
        $this->zipDirectory($outputAbsoluteDir, $zipAbsolutePath);

        Storage::delete([$this->templatePath, $this->dataPath, $manifestRelativePath]);

        Cache::put($cacheKey, [
            'event_id' => $this->eventId,
            'batch_id' => $this->batchId,
            'status' => 'completed',
            'message' => 'Certificate generation completed.',
            'zip_path' => $zipRelativePath,
            'batch_dir' => $batchRelativeDir,
            'summary' => $stats,
            'updated_at' => now()->toIso8601String(),
        ], now()->addHours(6));
    }

    public function failed(Throwable $exception): void
    {
        $cacheKey = $this->cacheKey();
        $batchRelativeDir = "tmp/certificates/{$this->eventId}/{$this->batchId}";
        $message = $exception->getMessage();

        $this->recordFailure($message);

        Cache::put($cacheKey, [
            'event_id' => $this->eventId,
            'batch_id' => $this->batchId,
            'status' => 'failed',
            'message' => 'Certificate generation failed.',
            'error' => $message,
            'zip_path' => null,
            'batch_dir' => $batchRelativeDir,
            'updated_at' => now()->toIso8601String(),
        ], now()->addHours(6));
    }

    private function persistLogsAndQueueEmails(array $records): array
    {
        $success = 0;
        $fail = 0;

        foreach ($records as $record) {
            $status = strtolower((string) ($record['status'] ?? 'fail')) === 'success' ? 'success' : 'fail';
            $recipientEmail = $record['recipient_email'] ?? null;
            $recipientName = $record['recipient_name'] ?? null;
            $attachmentPath = $record['attachment_path'] ?? null;
            $filename = $record['filename'] ?? ($attachmentPath ? basename((string) $attachmentPath) : null);
            $error = $record['error_message'] ?? null;

            CertificateLog::create([
                'filename' => $filename,
                'recipient_email' => $recipientEmail,
                'status' => $status,
                'error_message' => $error,
                'processed_at' => now(),
            ]);

            if ($status === 'success') {
                $success++;
            } else {
                $fail++;
            }

            if ($status === 'success' && $recipientEmail && $attachmentPath && File::exists($attachmentPath)) {
                Mail::to($recipientEmail)->queue(
                    (new GeneratedCertificateMail(
                        $attachmentPath,
                        $filename ?? basename($attachmentPath),
                        $this->eventId
                    ))->withRecipientName($recipientName)
                );
            }
        }

        return [
            'total' => count($records),
            'success' => $success,
            'fail' => $fail,
        ];
    }

    private function readManifest(string $manifestAbsolutePath): array
    {
        if (!File::exists($manifestAbsolutePath)) {
            return [];
        }

        $decoded = json_decode((string) File::get($manifestAbsolutePath), true);

        return is_array($decoded) ? $decoded : [];
    }

    private function zipDirectory(string $sourceDir, string $zipPath): void
    {
        File::ensureDirectoryExists(dirname($zipPath));

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        foreach (File::allFiles($sourceDir) as $file) {
            $relative = ltrim(str_replace($sourceDir, '', $file->getRealPath()), DIRECTORY_SEPARATOR);
            $zip->addFile($file->getRealPath(), str_replace('\\', '/', $relative));
        }

        $zip->close();
    }

    private function recordFailure(string $message): void
    {
        CertificateLog::create([
            'filename' => null,
            'recipient_email' => null,
            'status' => 'fail',
            'error_message' => $message,
            'processed_at' => now(),
        ]);
    }

    private function cacheKey(): string
    {
        return "certificate_batch:{$this->batchId}";
    }

    private function runGeneratorProcess(array $baseArgs): void
    {
        $lastException = null;

        foreach ($this->resolvePythonCandidates() as $pythonExecutable) {
            $command = array_merge([$pythonExecutable], $baseArgs);

            try {
                $process = new Process($command, base_path());
                $process->setEnv($this->buildPythonProcessEnv());
                $process->setTimeout(0);
                $process->mustRun();
                return;
            } catch (ProcessFailedException $exception) {
                $lastException = $exception;
                $message = trim($exception->getProcess()->getErrorOutput() ?: $exception->getProcess()->getOutput()) ?: $exception->getMessage();

                if ($this->isMissingExecutableError($message)) {
                    continue;
                }

                throw $exception;
            }
        }

        if ($lastException instanceof ProcessFailedException) {
            throw $lastException;
        }

        throw new \RuntimeException('Unable to resolve a working Python executable for certificate generation.');
    }

    private function resolvePythonCandidates(): array
    {
        $configured = [
            (string) config('services.certificate_generator.python_path', ''),
            (string) config('services.certificate_generator.python', ''),
        ];

        $defaults = PHP_OS_FAMILY === 'Windows'
            ? ['py', 'python']
            : ['python3', 'python'];

        $candidates = array_merge($configured, $defaults);

        return array_values(array_filter(array_unique(array_map('trim', $candidates)), fn ($value) => $value !== ''));
    }

    private function isMissingExecutableError(string $message): bool
    {
        $normalized = strtolower($message);

        return str_contains($normalized, 'not recognized as an internal or external command')
            || str_contains($normalized, 'is not recognized as an internal or external command')
            || str_contains($normalized, 'no such file or directory')
            || str_contains($normalized, 'could not be found');
    }

    private function buildPythonProcessEnv(): array
    {
        $env = $_ENV;

        $configuredHashSeed = (string) config('services.certificate_generator.python_hash_seed', '0');
        $env['PYTHONHASHSEED'] = $configuredHashSeed === '' ? '0' : $configuredHashSeed;

        return $env;
    }
}
