<?php

namespace App\Http\Controllers;

use App\Repositories\RequestFormPivotRepo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PDFGeneratorController extends Controller
{
    protected RequestFormPivotRepo $requestFormPivotRepo;

    public function __construct(RequestFormPivotRepo $requestFormPivotRepo)
    {
        $this->requestFormPivotRepo = $requestFormPivotRepo;
    }
    /**
     * Stream or download a cached/generated PDF for the given RequestFormPivot.
     * Accepts an optional 'template' query parameter pointing to a Blade view.
     * Caches PDFs in public/generated-pdfs/{template-slug}/{id}.pdf and
     * regenerates when the model is updated (updated_at newer than file mtime).
     *
     * Query params:
     * - template: Blade view path (e.g., generator/pdf/printable-request-form)
     * - download: 1 to force download; default streams inline
     */
    public function downloadPdf(Request $request, $id)
    {
        $template = $request->query('template', 'generator/pdf/printable-request-form');
        $prefetch = filter_var($request->query('prefetch', false), FILTER_VALIDATE_BOOLEAN);
        $forceRefresh = filter_var($request->query('force_refresh', false), FILTER_VALIDATE_BOOLEAN);

        // Validate the view exists; if not, fall back to default
        if (!View::exists($template)) {
            $template = 'generator/pdf/printable-request-form';
        }
        
        $form = $this->requestFormPivotRepo->getForPdf($id);

        // Prepare cache path based on template and id
        $templateSlug = Str::slug($template);
        $cacheDir = public_path("generated-pdfs/{$templateSlug}");
        if (!File::exists($cacheDir)) {
            File::makeDirectory($cacheDir, 0775, true);
        }
        $cacheFile = $cacheDir . DIRECTORY_SEPARATOR . $id . '.pdf';

        if ($forceRefresh && File::exists($cacheFile)) {
            File::delete($cacheFile);
        }

        $needsGenerate = true;
        if (File::exists($cacheFile)) {
            $fileMTime = File::lastModified($cacheFile);

            // Consider updated_at of pivot and related models
            $timestamps = [
                optional($form->updated_at)->getTimestamp() ?? 0,
                optional(optional($form->requester)->updated_at)->getTimestamp() ?? 0,
                optional(optional($form->request_form)->updated_at)->getTimestamp() ?? 0,
            ];
            $latestDataTs = max($timestamps);

            if ($latestDataTs >= $fileMTime) {
                // Data is newer; delete stale cached PDF
                File::delete($cacheFile);
                $needsGenerate = true;
            } else {
                $needsGenerate = false;
            }
        }

        if ($needsGenerate) {
            try {
                $pdfBinary = Pdf::loadView($template, compact('form'))->output();

                if (!$pdfBinary) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Failed to generate PDF output.',
                    ], 500);
                }

                $tmpCacheFile = $cacheFile . '.tmp';
                if (File::exists($tmpCacheFile)) {
                    File::delete($tmpCacheFile);
                }

                File::put($tmpCacheFile, $pdfBinary);

                if (!File::exists($tmpCacheFile) || File::size($tmpCacheFile) <= 0) {
                    if (File::exists($tmpCacheFile)) {
                        File::delete($tmpCacheFile);
                    }

                    return response()->json([
                        'status' => 'error',
                        'message' => 'Generated PDF is empty.',
                    ], 500);
                }

                if (File::exists($cacheFile)) {
                    File::delete($cacheFile);
                }

                File::move($tmpCacheFile, $cacheFile);
            } catch (\Throwable $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unable to generate PDF.',
                    'details' => $e->getMessage(),
                ], 500);
            }
        }

        if (!File::exists($cacheFile) || File::size($cacheFile) <= 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'PDF file is missing or invalid.',
            ], 500);
        }

        if ($prefetch) {
            return response()->json([
                'ready' => true,
                'url' => route('forms.generate.pdf', ['id' => $id, 'template' => $template]),
                'download_url' => route('forms.generate.pdf', ['id' => $id, 'template' => $template, 'download' => 1]),
            ]);
        }

        $download = filter_var($request->query('download', false), FILTER_VALIDATE_BOOLEAN);
        $downloadName = $this->buildDefaultFilename($form) . '.pdf';

        if ($download) {
            return response()->download($cacheFile, $downloadName, [
                'Content-Type' => 'application/pdf',
            ]);
        }

        // Stream inline; leverage the cached file so it doesn't regenerate
        return response()->file($cacheFile, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $downloadName . '"',
        ]);
    }

    /**
     * Generate a PDF of barcode labels (5cm x 3cm per page) from provided SVGs.
     * Expects a JSON payload with a labels array.
     */
    public function downloadBarcodePdf(Request $request)
    {
        $validated = $request->validate([
            'labels' => ['required', 'array', 'min:1'],
            'labels.*.name' => ['required', 'string'],
            'labels.*.brand' => ['nullable', 'string'],
            'labels.*.barcode' => ['required', 'string'],
            'labels.*.svg' => ['required', 'string'],
        ]);

        $labels = $validated['labels'];

        $pdf = Pdf::loadView('generator/pdf/barcode-labels', compact('labels'));
        // 5cm x 3cm label size in points
        $pdf->setPaper([0, 0, 141.73, 85.04], 'portrait');

        $downloadName = 'barcodes_' . Carbon::now()->format('Ymd_His') . '.pdf';

        return $pdf->download($downloadName);
    }

    /**
     * Build default filename using Fullname_Datenow_timestamp convention.
     */
    protected function buildDefaultFilename($form): string
    {
        $fullName = $this->extractFullName($form);
        $now = Carbon::now()->format('Ymd_His');
        // Sanitize filename
        $safeName = preg_replace('/[^A-Za-z0-9\-_. ]/', '', $fullName);
        $safeName = trim(preg_replace('/\s+/', ' ', $safeName));
        $safeName = str_replace(' ', '_', $safeName);
        return $safeName . '_' . $now;
    }

    /**
     * Try to extract a reasonable full name from related requester data.
     */
    protected function extractFullName($form): string
    {
        $fallback = 'RequestForm-' . ($form->id ?? 'unknown');
        if (!isset($form->requester)) return $fallback;

        $req = $form->requester;
        foreach (['full_name', 'fullname', 'name'] as $prop) {
            if (isset($req->{$prop}) && $req->{$prop}) return (string) $req->{$prop};
        }

        $first = isset($req->first_name) ? (string) $req->first_name : '';
        $last = isset($req->last_name) ? (string) $req->last_name : '';
        $combined = trim($first . ' ' . $last);
        return $combined !== '' ? $combined : $fallback;
    }
}
