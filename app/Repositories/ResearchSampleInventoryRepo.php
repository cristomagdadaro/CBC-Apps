<?php

namespace App\Repositories;

use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchSampleInventoryLog;
use Illuminate\Support\Collection;

class ResearchSampleInventoryRepo
{
    public function listSamples(array $filters = []): Collection
    {
        $query = ResearchSample::query()->with('experiment.study.project');

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('uid', 'like', "%{$search}%")
                    ->orWhere('accession_name', 'like', "%{$search}%")
                    ->orWhere('pr_code', 'like', "%{$search}%")
                    ->orWhere('line_label', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['experiment_id'])) {
            $query->where('experiment_id', $filters['experiment_id']);
        }

        return $query->latest('updated_at')->limit((int) ($filters['limit'] ?? 100))->get();
    }

    public function findByUid(string $uid): ?ResearchSample
    {
        return ResearchSample::query()
            ->with([
                'experiment.study.project',
                'monitoringRecords' => fn ($query) => $query->latest('recorded_on')->latest('id')->limit(10),
            ])
            ->where('uid', $uid)
            ->first();
    }

    public function logAction(ResearchSample $sample, string $action, ?string $barcodeValue, ?string $qrPayload, array $context = [], ?string $performedBy = null): ResearchSampleInventoryLog
    {
        /** @var ResearchSampleInventoryLog $log */
        $log = ResearchSampleInventoryLog::query()->create([
            'sample_id' => $sample->id,
            'action' => $action,
            'barcode_value' => $barcodeValue,
            'qr_payload' => $qrPayload,
            'context' => $context,
            'performed_by' => $performedBy,
        ]);

        return $log;
    }
}
