<?php

namespace App\Repositories;

use App\Models\Item;
use App\Models\Requester;
use App\Models\RequestFormPivot;
use App\Models\UseRequestForm;
use App\Services\LabRequest\RequestLifecycleService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Support\Collection;
use App\Pipelines\RequestApproval\AuthorizeApprovalAction;
use App\Pipelines\RequestApproval\PrepareApprovalPayload;
use App\Pipelines\RequestApproval\PersistApproval;

class RequestFormPivotRepo extends AbstractRepoService
{
    public array $appendWith = ['requester', 'request_form'];

    public function __construct(
        RequestFormPivot $model,
        private readonly OptionRepo $optionRepo,
        private readonly RequestLifecycleService $lifecycleService,
    )
    {
        parent::__construct($model);
    }

    public function search(Collection $parameters, bool $withPagination = true, bool $isTrashed = false)
    {
        $result = parent::search($parameters, $withPagination, $isTrashed);

        $this->hydrateRequestFormLabels($result);

        return $result;
    }

    public function createRequestPivot(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $requester = Requester::query()->create([
                'name' => $data['name'],
                'affiliation' => $data['affiliation'],
                'philrice_id' => $data['requester_philrice_id'] ?? null,
                'email' => $data['email'],
                'position' => $data['position'] ?? null,
                'phone' => $data['phone'],
            ]);

            $this->lifecycleService->syncReturningPersonnel($data['requester_philrice_id'] ?? null, $data);

            $requestTypes = $data['request_type'];
            if (!is_array($requestTypes)) {
                $requestTypes = array_filter([$requestTypes]);
            }

            $requestForm = UseRequestForm::query()->create([
                'request_type' => array_values(array_unique($requestTypes)),
                'request_details' => $data['request_details'] ?? null,
                'request_purpose' => $data['request_purpose'],
                'project_title' => $data['project_title'] ?? null,
                'date_of_use' => $data['date_of_use'],
                'date_of_use_end' => $data['date_of_use_end'] ?? null,
                'time_of_use' => $data['time_of_use'],
                'time_of_use_end' => $data['time_of_use_end'] ?? null,
                'labs_to_use' => $this->sanitizeSelectionValues($data['labs_to_use'] ?? []),
                'equipments_to_use' => $this->sanitizeSelectionValues($data['equipments_to_use'] ?? []),
                'consumables_to_use' => $this->sanitizeSelectionValues($data['consumables_to_use'] ?? []),
            ]);

            $pivot = new RequestFormPivot([
                'requester_id' => $requester->id,
                'form_id' => $requestForm->id,
                'agreed_clause_1' => (bool) ($data['agreed_clause_1'] ?? false),
                'agreed_clause_2' => (bool) ($data['agreed_clause_2'] ?? false),
                'agreed_clause_3' => (bool) ($data['agreed_clause_3'] ?? false),
            ]);

            $pivot->forceFill([
                'request_status' => RequestFormPivot::STATUS_PENDING,
                'approval_constraint' => null,
                'disapproved_remarks' => null,
                'approved_by' => null,
            ])->save();

            return $pivot->fresh(['requester', 'request_form']);
        });
    }

    public function getGuestFormById(?string $requestId): ?RequestFormPivot
    {
        if (!$requestId) {
            return null;
        }

        $record = $this->model
            ->newQuery()
            ->where('id', $requestId)
            ->with(['requester','request_form'])
            ->first();

        if (!$record) {
            return null;
        }

        $this->hydrateRequestFormLabels(collect([$record]));

        return $record->makeHidden(['disapproved_remarks', 'approval_constraint', 'approved_by']);
    }

    public function getForPdf(string $id): RequestFormPivot
    {
        /** @var RequestFormPivot $record */
        $record = $this->model
            ->newQuery()
            ->with(['requester', 'request_form'])
            ->findOrFail($id);

        return $record;
    }

    public function updateApprovalWithPipeline(string $id, array $validated): Model
    {
        return DB::transaction(function () use ($id, $validated) {
            $model = $this->model->findOrFail($id);

            $context = app(Pipeline::class)
                ->send([
                    'model' => $model,
                    'validated' => $validated,
                ])
                ->through([
                    AuthorizeApprovalAction::class,
                    PrepareApprovalPayload::class,
                    PersistApproval::class,
                ])
                ->thenReturn();

            $this->lifecycleService->sendTransitionNotification(
                $context['model'],
                $context['previous_status'] ?? null,
                $context['requested_status'] ?? null,
            );

            return $context['model'];
        });
    }

    private function hydrateRequestFormLabels(mixed $result): void
    {
        $collection = match (true) {
            $result instanceof AbstractPaginator => $result->getCollection(),
            $result instanceof Collection => $result,
            is_array($result) && array_key_exists('data', $result) && $result['data'] instanceof Collection => $result['data'],
            default => null,
        };

        if (!$collection instanceof Collection || $collection->isEmpty()) {
            return;
        }

        $forms = $collection
            ->pluck('request_form')
            ->filter(fn ($form) => $form instanceof UseRequestForm)
            ->values();

        if ($forms->isEmpty()) {
            return;
        }

        $itemIds = $forms
            ->flatMap(fn (UseRequestForm $form) => array_merge(
                $this->sanitizeSelectionValues($form->equipments_to_use ?? []),
                $this->sanitizeSelectionValues($form->consumables_to_use ?? []),
            ))
            ->unique()
            ->values();

        $items = Item::query()
            ->whereIn('id', $itemIds)
            ->get(['id', 'name', 'description', 'brand'])
            ->keyBy('id');

        $laboratories = $this->optionRepo
            ->getLaboratories()
            ->mapWithKeys(fn ($lab) => [(string) ($lab['value'] ?? '') => $lab['label'] ?? null]);

        $forms->each(function (UseRequestForm $form) use ($items, $laboratories) {
            $equipmentLabels = collect($this->sanitizeSelectionValues($form->equipments_to_use ?? []))
                ->map(function (string $id) use ($items) {
                    $item = $items->get($id);

                    if (!$item) {
                        return null;
                    }

                    return trim($item->name . ($item->brand ? " - {$item->brand}" : '') . ($item->description ? " ({$item->description})" : ''));
                })
                ->filter()
                ->values()
                ->all();

            $consumableLabels = collect($this->sanitizeSelectionValues($form->consumables_to_use ?? []))
                ->map(function (string $id) use ($items) {
                    $item = $items->get($id);

                    if (!$item) {
                        return null;
                    }

                    return trim($item->name . ($item->brand ? " - {$item->brand}" : '') . ($item->description ? " ({$item->description})" : ''));
                })
                ->filter()
                ->values()
                ->all();

            $laboratoryLabels = collect($this->sanitizeSelectionValues($form->labs_to_use ?? []))
                ->map(fn (string $value) => $laboratories->get($value))
                ->filter()
                ->values()
                ->all();

            $form->setResolvedSelectionLabels([
                'equipments' => $equipmentLabels,
                'laboratories' => $laboratoryLabels,
                'consumables' => $consumableLabels,
            ]);
        });
    }

    private function sanitizeSelectionValues(array $values): array
    {
        return collect($values)
            ->filter(fn ($value) => is_scalar($value) && trim((string) $value) !== '')
            ->map(fn ($value) => mb_substr(trim((string) $value), 0, 191))
            ->unique()
            ->values()
            ->all();
    }
}
