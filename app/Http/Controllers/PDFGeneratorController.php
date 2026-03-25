<?php

namespace App\Http\Controllers;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use App\Repositories\RequestFormPivotRepo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Milon\Barcode\DNS1D;

class PDFGeneratorController extends BaseController
{
    public function __construct(RequestFormPivotRepo $requestFormPivotRepo)
    {
        $this->service = $requestFormPivotRepo;
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
        
        $form = $this->requestFormPivotRepo()->getForPdf($id);
        $this->authorize('view', $form);

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
                $pdfBinary = Pdf::loadView($template, compact('form') + ['forPdf' => true])->output();

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
     * Unified PDF generator for various document types (forms, labels, reports, etc.)
     * Accepts type parameter to route to appropriate template and validation.
     * 
     * Query/Body params:
     * - type: 'barcode-labels', 'request-form', etc.
     * - template: (optional) custom Blade template override
     * - printMode: (optional) 'barcode', 'qr', 'both' for labels
     * - paperWidth/paperHeight: (optional) custom paper dimensions in cm
     * - labels/data: content to render
     */
    public function generatePdf(Request $request)
    {
        $type = $request->input('type', 'barcode-labels');
        $template = $request->input('template');
        
        // Route type to appropriate handler
        return match ($type) {
            'barcode-labels' => $this->generateBarcodeLabelsPdf($request, $template),
            default => response()->json(['error' => 'Unknown PDF type'], 400),
        };
    }

    /**
     * Generate barcode/QR label PDFs with flexible layout and content.
     * Supports barcode-only, QR-only, or mixed mode labels.
     */
    protected function generateBarcodeLabelsPdf(Request $request, ?string $template = null)
    {
        // Render all visual assets on backend for reliable DomPDF output
        $printMode = $request->input('printMode', 'barcode');
        $validated = $request->validate([
            'labels' => ['required', 'array', 'min:1'],
            'labels.*.name' => ['required', 'string'],
            'labels.*.brand' => ['nullable', 'string'],
            'labels.*.barcode' => ['required', 'string'],
            'labels.*.qrUrl' => ['nullable', 'string'],
            'paperWidth' => ['nullable', 'numeric', 'min:1'],
            'paperHeight' => ['nullable', 'numeric', 'min:1'],
            'qrSize' => ['nullable', 'numeric', 'min:20', 'max:1000'],
            'barcodeHeight' => ['nullable', 'numeric', 'min:10', 'max:1000'],
        ]);

        $labels = $validated['labels'];
        $paperWidth = $validated['paperWidth'] ?? 5;
        $paperHeight = $validated['paperHeight'] ?? 3;
        $qrSize = (int) ($validated['qrSize'] ?? 56);
        $barcodeHeight = (int) ($validated['barcodeHeight'] ?? 30);
        $barcodeGenerator = new DNS1D();

        $qrWriter = new Writer(
            new ImageRenderer(
                new RendererStyle($qrSize),
                new SvgImageBackEnd()
            )
        );

        $labels = array_map(function (array $label) use ($barcodeGenerator, $qrWriter, $printMode, $qrSize, $barcodeHeight) {
            $barcodeValue = (string) ($label['barcode'] ?? '');
            $qrUrl = $label['qrUrl'] ?? null;

            if ($barcodeValue !== '' && $printMode !== 'qr') {
                $barcodeBase64 = $barcodeGenerator->getBarcodePNG($barcodeValue, 'C128', 2, $barcodeHeight);
                $label['barcodeDataUri'] = 'data:image/png;base64,' . $barcodeBase64;
            } else {
                $label['barcodeDataUri'] = null;
            }

            if (is_string($qrUrl) && trim($qrUrl) !== '' && $printMode !== 'barcode') {
                $effectiveQrSize = $printMode === 'both' ? max(24, (int) floor($qrSize * 0.72)) : $qrSize;
                $qrSvg = $effectiveQrSize === $qrSize
                    ? $qrWriter->writeString($qrUrl)
                    : (new Writer(
                        new ImageRenderer(
                            new RendererStyle($effectiveQrSize),
                            new SvgImageBackEnd()
                        )
                    ))->writeString($qrUrl);
                $label['qrSvg'] = $qrSvg;
                $label['qrSizePx'] = $effectiveQrSize;
            } else {
                $label['qrSvg'] = null;
                $label['qrSizePx'] = $qrSize;
            }

            return $label;
        }, $labels);

        $template = $template ?? 'generator/pdf/barcode-labels';

        $pdf = Pdf::loadView($template, compact('labels', 'printMode', 'paperWidth', 'paperHeight', 'qrSize', 'barcodeHeight'));
        
        // Convert cm to points (1cm = 28.3465 points)
        $widthPoints = $paperWidth * 28.3465;
        $heightPoints = $paperHeight * 28.3465;
        $pdf->setPaper([0, 0, $widthPoints, $heightPoints], 'portrait');

        $downloadName = 'barcodes_' . Carbon::now()->format('Ymd_His') . '.pdf';

        return $pdf->download($downloadName);
    }

    /**
     * Alias for generatePdf() - allows both routes to work
     */
    public function downloadGeneratedPdf(Request $request)
    {
        return $this->generatePdf($request);
    }

    /**
     * Generate a PDF of barcode labels (5cm x 3cm per page) from provided SVGs.
     * Deprecated: Use generatePdf() with type='barcode-labels' instead.
     */
    public function downloadBarcodePdf(Request $request)
    {
        return $this->generateBarcodeLabelsPdf($request);
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

    protected function requestFormPivotRepo(): RequestFormPivotRepo
    {
        return $this->requireService();
    }
}
