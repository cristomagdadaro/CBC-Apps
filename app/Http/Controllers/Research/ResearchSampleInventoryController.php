<?php

namespace App\Http\Controllers\Research;

use App\Http\Controllers\BaseController;
use App\Repositories\ResearchSampleInventoryRepo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResearchSampleInventoryController extends BaseController
{
    public function __construct(private readonly ResearchSampleInventoryRepo $inventoryRepo)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $samples = $this->inventoryRepo->listSamples([
            'search' => $request->query('search'),
            'experiment_id' => $request->query('experiment_id'),
            'limit' => (int) $request->query('limit', 100),
        ], $request->user());

        return response()->json([
            'data' => $samples,
        ]);
    }

    public function lookup(string $uid, Request $request): JsonResponse
    {
        $sample = $this->inventoryRepo->findByUid($uid, $request->user());

        if (!$sample) {
            return response()->json([
                'message' => 'Sample not found.',
            ], 404);
        }

        $this->inventoryRepo->logAction(
            $sample,
            'scan_lookup',
            $uid,
            $request->input('qr_payload'),
            [
                'source' => $request->input('source', 'manual_lookup'),
                'ip' => $request->ip(),
            ],
            $request->user()?->id,
        );

        return response()->json([
            'data' => $sample,
        ]);
    }

    public function scan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payload' => ['required', 'string', 'max:500'],
            'source' => ['nullable', 'string', 'max:50'],
        ]);

        $payload = trim($validated['payload']);
        $uid = $this->extractUidFromPayload($payload);

        $sample = $this->inventoryRepo->findByUid($uid, $request->user());

        if (!$sample) {
            return response()->json([
                'message' => 'Scanned code does not match a research sample.',
            ], 404);
        }

        $this->inventoryRepo->logAction(
            $sample,
            'scan_lookup',
            $uid,
            $payload,
            [
                'source' => $validated['source'] ?? 'camera_scan',
                'ip' => $request->ip(),
            ],
            $request->user()?->id,
        );

        return response()->json([
            'data' => $sample,
        ]);
    }

    public function printLabels(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sample_ids' => ['required', 'array', 'min:1'],
            'sample_ids.*' => ['integer'],
        ]);

        $samples = $this->inventoryRepo
            ->listSamples([
                'limit' => 500,
                'sample_ids' => $validated['sample_ids'],
            ], $request->user());

        foreach ($samples as $sample) {
            $this->inventoryRepo->logAction(
                $sample,
                'print_label',
                $sample->uid,
                'sample:' . $sample->uid,
                [
                    'ip' => $request->ip(),
                ],
                $request->user()?->id,
            );
        }

        return response()->json([
            'data' => $samples,
        ]);
    }

    public function show(string $uid, Request $request): JsonResponse
    {
        $sample = $this->inventoryRepo->findPassportByUid($uid, $request->user());

        if (!$sample) {
            return response()->json([
                'message' => 'Sample not found.',
            ], 404);
        }

        return response()->json([
            'data' => $sample,
        ]);
    }

    private function extractUidFromPayload(string $payload): string
    {
        if (str_contains($payload, '|')) {
            $firstSegment = trim(explode('|', $payload)[0]);

            return trim(str_replace('sample:', '', $firstSegment));
        }

        if (str_starts_with($payload, 'http://') || str_starts_with($payload, 'https://')) {
            $path = trim((string) parse_url($payload, PHP_URL_PATH), '/');
            $segments = array_values(array_filter(explode('/', $path)));
            $lastSegment = end($segments);

            if ($lastSegment !== false && $lastSegment !== '') {
                return (string) $lastSegment;
            }
        }

        return trim(str_replace('sample:', '', $payload));
    }
}
