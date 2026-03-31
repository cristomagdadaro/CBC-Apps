<?php

namespace App\Repositories;

use App\Events\ResearchSampleInventoryChanged;
use App\Models\Research\ResearchSample;
use App\Models\Research\ResearchSampleInventoryLog;
use App\Models\User;
use App\Services\Research\ResearchAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ResearchSampleInventoryRepo
{
    public function __construct(private readonly ResearchAccessService $accessService)
    {
    }

    public function listSamples(array $filters = [], ?User $user = null): Collection
    {
        $query = $this->baseQuery($user);

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

        if (! empty($filters['sample_ids']) && is_array($filters['sample_ids'])) {
            $query->whereIn('id', $filters['sample_ids']);
        }

        return $query->latest('updated_at')->limit((int) ($filters['limit'] ?? 100))->get();
    }

    public function findByUid(string $uid, ?User $user = null): ?ResearchSample
    {
        return $this->baseQuery($user)
            ->with([
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

        $log->loadMissing('sample');

        event(new ResearchSampleInventoryChanged($log));

        return $log;
    }

    protected function baseQuery(?User $user): Builder
    {
        return ResearchSample::query()
            ->with('experiment.study.project')
            ->whereHas('experiment.study.project', function (Builder $query) use ($user) {
                $query->whereIn('research_projects.id', $this->accessService->visibleProjectIdsQuery($user));
            });
    }
}
