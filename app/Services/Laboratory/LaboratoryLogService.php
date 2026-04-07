<?php

namespace App\Services\Laboratory;

use App\Events\EquipmentLogChanged;
use App\Mail\LaboratoryEquipmentLogOverdueMail;
use App\Models\Item;
use App\Models\LaboratoryEquipmentLocationSurvey;
use App\Models\LaboratoryEquipmentLog;
use App\Models\Personnel;
use App\Models\Transaction;
use App\Repositories\OptionRepo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LaboratoryLogService
{
    public function __construct(private readonly OptionRepo $optionRepo)
    {

    }

    public function resolveEquipmentId(string $identifier): ?string
    {
        // 1️ If UUID — could be log ID or equipment ID
        if (Str::isUuid($identifier)) {
            // Try laboratory equipment log
            $equipmentId = LaboratoryEquipmentLog::where('id', $identifier)
                ->value('equipment_id');

            return $equipmentId ?? $identifier;
        }

        // 2 Try barcode or barcode_prri (via transactions table directly)
        $itemId = Transaction::withTrashed()
            ->where('barcode', $identifier)
            ->orWhere('barcode_prri', $identifier)
            ->value('item_id');

        if ($itemId) {
            return $itemId;
        }

        // 3 Try transaction ID
        $itemId = Transaction::withTrashed()->where('id', $identifier)
            ->value('item_id');

        return $itemId ?: null;
    }


    public function listEligibleEquipment(?string $search = null, string $equipmentType = 'laboratory'): Collection
    {
        $categoryIds = $this->categoryIdsForType($equipmentType);

        $query = Transaction::withTrashed()
            ->select([
                'items.id as equipment_id',
                'items.name',
                'items.brand',
                'items.description',
                'items.category_id',
                'categories.name as category_name',
                DB::raw('MAX(transactions.barcode) as barcode'),
                DB::raw('MAX(transactions.barcode_prri) as barcode_prri'),
            ])
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->where(function (Builder $query) use ($categoryIds, $equipmentType) {
                $query->whereIn('categories.id', $categoryIds);

                if ($equipmentType === 'laboratory') {
                    $query->orWhere('categories.name', 'Laboratory Equipment');
                }
            });

        if ($search) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('items.id', 'like', "%{$search}%")
                    ->orWhere('items.name', 'like', "%{$search}%")
                    ->orWhere('items.brand', 'like', "%{$search}%")
                    ->orWhere('transactions.barcode', 'like', "%{$search}%")
                    ->orWhere('transactions.barcode_prri', 'like', "%{$search}%");
            });
        }

        return $query->groupBy('items.id', 'items.name', 'items.brand', 'items.description', 'items.category_id', 'categories.name')
            ->orderBy('items.name')
            ->get();
    }

    public function getEquipmentDetails(string $equipmentId, string $equipmentType = 'laboratory'): array
    {
        $equipment = $this->findEligibleEquipment($equipmentId, $equipmentType);

        if (!$equipment) {
            // Check if item exists but is wrong category
            $item = $this->findEquipmentForValidation($equipmentId);
            if ($item) {
                $categoryName = $item->category?->name ?? 'Unknown Category';
                abort(422, "Sorry, this is a {$categoryName} item. You can only log {$this->equipmentLabel($equipmentType)}.");
            }
            abort(404, 'Equipment not found.');
        }

        $activeLog = $this->getActiveLog($equipmentId);
        $resolvedLocation = $this->resolveEquipmentLocation($equipmentId, $equipment->barcode ?? null);

        return [
            'equipment' => $equipment,
            'active_log' => $this->serializeActiveLog($activeLog),
            'allowed_actions' => $activeLog ? ['check-out'] : ['check-in'],
            'purpose_suggestions' => $this->getPurposeSuggestions($equipmentId),
            'current_location' => $resolvedLocation,
            'storage_location_options' => $this->getStorageLocationOptions(),
            'max_end_use_hours' => (int) config('laboratory.max_end_use_hours', 24),
        ];
    }

    public function checkIn(string $equipmentId, array $payload, string $equipmentType = 'laboratory'): LaboratoryEquipmentLog
    {
        $equipment = $this->findEligibleEquipment($equipmentId, $equipmentType);
        if (!$equipment) {
            // Check if item exists but is wrong category
            $item = $this->findEquipmentForValidation($equipmentId);
            if ($item) {
                $categoryName = $item->category?->name ?? 'Unknown Category';
                abort(422, "Sorry, this is a {$categoryName} item. You can only log {$this->equipmentLabel($equipmentType)}.");
            }
            abort(404, 'Equipment not found.');
        }

        $personnel = $this->resolvePersonnelFromPayload($payload);

        if ($personnel->updated_at === null) {
            abort(409, 'Please update your personnel information before checking in equipment.');
        }

        if (! filled($personnel->email)) {
            abort(409, 'Please provide your email before checking in equipment.');
        }

        return DB::transaction(function () use ($equipmentId, $payload, $personnel, $equipmentType) {
            $activeLog = $this->lockActiveLog($equipmentId);
            if ($activeLog) {
                abort(409, 'Equipment already has an active log.');
            }

            $log = new LaboratoryEquipmentLog();
            $log->equipment_id = $equipmentId;
            $log->personnel_id = $personnel->id;
            $log->status = 'active';
            $log->started_at = Carbon::now();
            $log->end_use_at = Carbon::parse($payload['end_use_at']);
            $log->purpose = $payload['purpose'] ?? null;
            $log->active_lock = true;
            $log->checked_in_by = optional(auth()->user())->id;

            try {
                $log->save();
            } catch (QueryException $exception) {
                if (str_contains($exception->getMessage(), 'active_lock_key')) {
                    abort(409, 'Equipment already has an active log.');
                }
                throw $exception;
            }

            $freshLog = $log->fresh(['personnel', 'equipment']);
            event(new EquipmentLogChanged('created', $equipmentType, $equipmentId, $freshLog));

            return $freshLog;
        });
    }

    public function checkOut(string $equipmentId, array $payload, string $equipmentType = 'laboratory'): LaboratoryEquipmentLog
    {
        $equipment = $this->findEligibleEquipment($equipmentId, $equipmentType);
        if (!$equipment) {
            // Check if item exists but is wrong category
            $item = $this->findEquipmentForValidation($equipmentId);
            if ($item) {
                $categoryName = $item->category?->name ?? 'Unknown Category';
                abort(422, "Sorry, this is a {$categoryName} item. You can only log {$this->equipmentLabel($equipmentType)}.");
            }
            abort(404, 'Equipment not found.');
        }

        return DB::transaction(function () use ($equipmentId, $payload, $equipmentType) {
            $activeLog = $this->lockActiveLog($equipmentId);
            if (!$activeLog) {
                abort(409, 'No active log found for this equipment.');
            }

            $isAdminOverride = $this->requestedAdminOverride($payload);
            if (!$isAdminOverride) {
                $personnel = $this->resolvePersonnelFromPayload($payload);

                if ($personnel->id !== $activeLog->personnel_id) {
                    abort(403, 'Only the original check-in personnel can check out this equipment.');
                }
            }

            $activeLog->status = 'completed';
            $activeLog->actual_end_at = Carbon::now();
            $activeLog->active_lock = false;
            $activeLog->checked_out_by = optional(auth()->user())->id;
            $activeLog->save();

            $freshLog = $activeLog->fresh(['personnel', 'equipment']);
            event(new EquipmentLogChanged('completed', $equipmentType, $equipmentId, $freshLog));

            return $freshLog;
        });
    }

    public function updateEndUse(string $equipmentId, array $payload, string $equipmentType = 'laboratory'): LaboratoryEquipmentLog
    {
        $equipment = $this->findEligibleEquipment($equipmentId, $equipmentType);
        if (!$equipment) {
            $item = $this->findEquipmentForValidation($equipmentId);
            if ($item) {
                $categoryName = $item->category?->name ?? 'Unknown Category';
                abort(422, "Sorry, this is a {$categoryName} item. You can only log {$this->equipmentLabel($equipmentType)}.");
            }
            abort(404, 'Equipment not found.');
        }

        $personnel = $this->resolvePersonnelFromPayload($payload);

        return DB::transaction(function () use ($equipmentId, $payload, $personnel, $equipmentType) {
            $activeLog = $this->lockActiveLog($equipmentId);
            if (!$activeLog) {
                abort(409, 'No active log found for this equipment.');
            }

            if ($personnel->id !== $activeLog->personnel_id) {
                abort(403, 'Only the original check-in personnel can update estimated end of use.');
            }

            $activeLog->end_use_at = Carbon::parse($payload['end_use_at']);
            $activeLog->save();

            $freshLog = $activeLog->fresh(['personnel', 'equipment']);
            event(new EquipmentLogChanged('end_use_updated', $equipmentType, $equipmentId, $freshLog));

            return $freshLog;
        });
    }

    public function reportTemporaryLocation(string $equipmentId, array $payload, string $equipmentType = 'laboratory'): LaboratoryEquipmentLocationSurvey
    {
        $equipment = $this->findEligibleEquipment($equipmentId, $equipmentType);
        if (!$equipment) {
            $item = $this->findEquipmentForValidation($equipmentId);
            if ($item) {
                $categoryName = $item->category?->name ?? 'Unknown Category';
                abort(422, "Sorry, this is a {$categoryName} item. You can only log {$this->equipmentLabel($equipmentType)}.");
            }
            abort(404, 'Equipment not found.');
        }

        $personnel = $this->resolvePersonnelFromPayload($payload);

        return DB::transaction(function () use ($equipmentId, $payload, $personnel, $equipmentType) {
            $survey = LaboratoryEquipmentLocationSurvey::firstOrNew([
                'equipment_id' => $equipmentId,
            ]);

            $survey->personnel_id = $personnel->id;
            $survey->location_code = !empty($payload['location_code']) ? trim((string) $payload['location_code']) : null;
            $survey->location_label = trim((string) $payload['location_label']);
            $survey->reported_at = Carbon::now();
            $survey->save();

            $freshSurvey = $survey->fresh(['personnel', 'equipment']);
            $activeLog = $this->getActiveLog($equipmentId)?->loadMissing(['personnel', 'equipment']);
            event(new EquipmentLogChanged('location_reported', $equipmentType, $equipmentId, $activeLog, $freshSurvey));

            return $freshSurvey;
        });
    }

    public function markOverdue(): int
    {
        return DB::transaction(function () {
            $logs = LaboratoryEquipmentLog::query()
                ->with(['personnel', 'equipment.category'])
                ->where('status', 'active')
                ->where('end_use_at', '<', Carbon::now())
                ->lockForUpdate()
                ->get();

            foreach ($logs as $log) {
                $log->status = 'overdue';
                $log->active_lock = true;
                $log->save();

                $equipmentType = $this->determineEquipmentType($log->equipment);
                event(new EquipmentLogChanged(
                    action: 'overdue',
                    equipmentType: $equipmentType,
                    equipmentId: (string) $log->equipment_id,
                    log: $log->fresh(['personnel', 'equipment']),
                ));

                $this->sendOverdueEmailNotification($log, $equipmentType);
            }

            return $logs->count();
        });
    }

    public function getActiveEquipment($employee_id = null, string $equipmentType = 'laboratory'): Collection
    {
        $categoryIds = $this->categoryIdsForType($equipmentType);

        $query = LaboratoryEquipmentLog::with(['equipment', 'personnel'])
            ->whereIn('status', ['active', 'overdue'])
            ->whereHas('equipment.category', function (Builder $builder) use ($categoryIds, $equipmentType) {
                $builder->whereIn('categories.id', $categoryIds);

                if ($equipmentType === 'laboratory') {
                    $builder->orWhere('categories.name', 'Laboratory Equipment');
                }
            })
            ->orderBy('started_at');

        if ($employee_id) {
            $query->whereHas('personnel', function ($q) use ($employee_id) {
                $q->where('employee_id', $employee_id);
            });
        }

        return $query->get();
    }

    public function getDashboardMetrics(): array
    {
        $activeLogs = $this->getActiveEquipment();

        $overdueLogs = $this->getDashboardLogsByStatuses(['overdue']);
        $completedLogs = $this->getDashboardLogsByStatuses(['completed']);

        $this->enrichLogsWithLocationDetails($activeLogs, $overdueLogs, $completedLogs);

        $mostUsedRows = LaboratoryEquipmentLog::query()
            ->select([
                'equipment_id',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw($this->totalDurationExpression()),
            ])
            ->whereIn('status', ['completed', 'overdue'])
            ->groupBy('equipment_id')
            ->orderByDesc('usage_count')
            ->limit(10)
            ->get();

        $equipmentMap = Item::whereIn('id', $mostUsedRows->pluck('equipment_id'))
            ->get()
            ->keyBy('id');

        $mostUsed = $mostUsedRows->map(function ($row) use ($equipmentMap) {
            $equipment = $equipmentMap->get($row->equipment_id);
            return [
                'equipment_id' => $row->equipment_id,
                'equipment_name' => $equipment?->name,
                'usage_count' => (int) $row->usage_count,
                'total_duration_seconds' => (int) $row->total_duration_seconds,
            ];
        });

        $heatmap = LaboratoryEquipmentLog::query()
            ->select([
                DB::raw('DAYOFWEEK(started_at) as day_of_week'),
                DB::raw('HOUR(started_at) as hour_of_day'),
                DB::raw('COUNT(*) as usage_count'),
            ])
            ->groupBy('day_of_week', 'hour_of_day')
            ->get();

        return [
            'active' => $activeLogs,
            'overdue' => $overdueLogs,
            'completed' => $completedLogs,
            'most_used' => $mostUsed,
            'heatmap' => $heatmap,
        ];
    }

    private function getDashboardLogsByStatuses(array $statuses, string $equipmentType = 'laboratory'): Collection
    {
        $categoryIds = $this->categoryIdsForType($equipmentType);

        return LaboratoryEquipmentLog::query()
            ->with(['equipment', 'personnel'])
            ->whereIn('status', $statuses)
            ->whereHas('equipment.category', function (Builder $builder) use ($categoryIds, $equipmentType) {
                $builder->whereIn('categories.id', $categoryIds);

                if ($equipmentType === 'laboratory') {
                    $builder->orWhere('categories.name', 'Laboratory Equipment');
                }
            })
            ->orderByDesc('actual_end_at')
            ->orderBy('end_use_at')
            ->get();
    }

    protected function totalDurationExpression(): string
    {
        return match (DB::getDriverName()) {
            'sqlite' => 'ROUND(SUM((julianday(COALESCE(actual_end_at, end_use_at)) - julianday(started_at)) * 86400)) as total_duration_seconds',
            default => 'SUM(TIMESTAMPDIFF(SECOND, started_at, COALESCE(actual_end_at, end_use_at))) as total_duration_seconds',
        };
    }

    public function enrichLogsWithLocationDetails(Collection ...$collections): void
    {
        $logs = collect($collections)
            ->flatten(1)
            ->filter();

        if ($logs->isEmpty()) {
            return;
        }

        $equipmentIds = $logs->pluck('equipment_id')
            ->filter()
            ->unique()
            ->values();

        if ($equipmentIds->isEmpty()) {
            return;
        }

        $latestBarcodeByEquipment = Transaction::query()
            ->select(['item_id', 'barcode'])
            ->whereIn('item_id', $equipmentIds)
            ->whereNotNull('barcode')
            ->orderByDesc('created_at')
            ->get()
            ->unique('item_id')
            ->keyBy('item_id');

        $temporaryLocationByEquipment = LaboratoryEquipmentLocationSurvey::query()
            ->whereIn('equipment_id', $equipmentIds)
            ->get()
            ->keyBy('equipment_id');

        $locationByCode = $this->buildStorageLocationLookup();

        $logs->each(function (LaboratoryEquipmentLog $log) use ($latestBarcodeByEquipment, $locationByCode, $temporaryLocationByEquipment) {
            $barcode = $latestBarcodeByEquipment->get($log->equipment_id)?->barcode;
            $temporary = $temporaryLocationByEquipment->get($log->equipment_id);

            if ($temporary) {
                $locationCode = $temporary->location_code;
                $locationLabel = $temporary->location_label ?: 'Unknown Location';
            } else {
                $locationCode = $this->extractLocationCodeFromBarcode($barcode);
                $locationLabel = $locationCode ? ($locationByCode[$locationCode] ?? 'Unknown Location') : 'Unknown Location';
            }

            $log->setAttribute('equipment_barcode', $barcode);
            $log->setAttribute('location_code', $locationCode);
            $log->setAttribute('location_label', $locationLabel);
        });
    }

    private function buildStorageLocationLookup(): array
    {
        return collect($this->optionRepo->getStorageLocations())
            ->mapWithKeys(function (array $location) {
                $name = $location['name'] ?? null;
                $label = $location['label'] ?? null;
                $code = $this->normalizeLocationCode($name);

                if (!$code || !$label) {
                    return [];
                }

                return [$code => $label];
            })
            ->toArray();
    }

    private function extractLocationCodeFromBarcode(?string $barcode): ?string
    {
        if (!$barcode) {
            return null;
        }

        if (!preg_match('/CBC-(\d+)-/i', $barcode, $matches)) {
            return null;
        }

        return str_pad($matches[1], 2, '0', STR_PAD_LEFT);
    }

    private function normalizeLocationCode(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_numeric($value)) {
            return str_pad((string) ((int) $value), 2, '0', STR_PAD_LEFT);
        }

        if (is_string($value) && preg_match('/(\d+)/', $value, $matches)) {
            return str_pad((string) ((int) $matches[1]), 2, '0', STR_PAD_LEFT);
        }

        return null;
    }

    private function findEquipmentForValidation(string $equipmentId): ?Item
    {
        return Item::query()
            ->with('category')
            ->where('id', $equipmentId)
            ->first();
    }

    /**
     * Determines if the given equipment ID corresponds to an eligible laboratory equipment item.
     * Only laboratory (7) and ICT (4) equipment categories are eligible, and the item must have at least one transaction record.
     * @param string $equipmentId
     */
    private function findEligibleEquipment(string $equipmentId, string $equipmentType = 'laboratory'): ?Item
    {
        $categoryIds = $this->categoryIdsForType($equipmentType);

        return Item::query()
            ->with('category')
            ->select('items.*')
            ->addSelect(
                DB::raw('(SELECT MAX(t.barcode) FROM transactions t WHERE t.item_id = items.id) as barcode'),
                DB::raw('(SELECT MAX(t.barcode_prri) FROM transactions t WHERE t.item_id = items.id AND t.barcode_prri IS NOT NULL) as barcode_prri')
            )
            ->where('items.id', $equipmentId)
            ->whereHas('category', function (Builder $query) use ($categoryIds, $equipmentType) {
                $query->whereIn('categories.id', $categoryIds);

                if ($equipmentType === 'laboratory') {
                    $query->orWhere('categories.name', 'Laboratory Equipment');
                }
            })
            ->whereHas('transactions', function (Builder $query) {
                $query->withTrashed();
            })
            ->first();
    }

    private function categoryIdsForType(string $equipmentType): array
    {
        return $equipmentType === 'ict' ? [4] : [7];
    }

    private function equipmentLabel(string $equipmentType): string
    {
        return $equipmentType === 'ict' ? 'ICT equipment' : 'laboratory equipment';
    }

    private function determineEquipmentType(?Item $item): string
    {
        $categoryId = (int) ($item?->category_id ?? 0);
        $categoryName = strtolower((string) ($item?->category?->name ?? ''));

        if ($categoryId === 4 || str_contains($categoryName, 'ict')) {
            return 'ict';
        }

        return 'laboratory';
    }

    private function getActiveLog(string $equipmentId): ?LaboratoryEquipmentLog
    {
        return LaboratoryEquipmentLog::query()
            ->with(['personnel', 'equipment'])
            ->where('equipment_id', $equipmentId)
            ->whereIn('status', ['active', 'overdue'])
            ->orderByDesc('started_at')
            ->first();
    }

    private function lockActiveLog(string $equipmentId): ?LaboratoryEquipmentLog
    {
        return LaboratoryEquipmentLog::where('equipment_id', $equipmentId)
            ->whereIn('status', ['active', 'overdue'])
            ->lockForUpdate()
            ->first();
    }

    private function getPurposeSuggestions(string $equipmentId): array
    {
        return LaboratoryEquipmentLog::query()
            ->where('equipment_id', $equipmentId)
            ->whereNotNull('purpose')
            ->where('purpose', '!=', '')
            ->orderByDesc('created_at')
            ->limit(50)
            ->pluck('purpose')
            ->map(fn (string $purpose) => trim($purpose))
            ->filter()
            ->unique()
            ->take(10)
            ->values()
            ->all();
    }

    private function getStorageLocationOptions(): array
    {
        return collect($this->optionRepo->getStorageLocations())
            ->map(fn (array $location) => $location['label'] ?? null)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function resolveEquipmentLocation(string $equipmentId, ?string $barcode): array
    {
        $temporary = LaboratoryEquipmentLocationSurvey::query()
            ->where('equipment_id', $equipmentId)
            ->first();

        if ($temporary) {
            return [
                'code' => $temporary->location_code,
                'label' => $temporary->location_label ?: 'Unknown Location',
                'source' => 'temporary',
            ];
        }

        $locationCode = $this->extractLocationCodeFromBarcode($barcode);
        $locationByCode = $this->buildStorageLocationLookup();

        return [
            'code' => $locationCode,
            'label' => $locationCode ? ($locationByCode[$locationCode] ?? 'Unknown Location') : 'Unknown Location',
            'source' => 'barcode',
        ];
    }
    private function requestedAdminOverride(array $payload): bool
    {
        return !empty($payload['admin_override']) && (auth()->user()?->is_admin ?? false);
    }

    private function resolvePersonnelFromPayload(array $payload): Personnel
    {
        $employeeId = trim((string) ($payload['employee_id'] ?? ''));

        if ($employeeId === '') {
            abort(422, 'Employee ID is required.');
        }

        $personnel = Personnel::query()
            ->preferredEmployeeId($employeeId)
            ->first();

        if (!$personnel) {
            abort(422, 'Personnel record not found for the provided employee ID.');
        }

        return $personnel;
    }

    private function serializeActiveLog(?LaboratoryEquipmentLog $activeLog): ?array
    {
        if (!$activeLog) {
            return null;
        }

        return [
            'id' => $activeLog->id,
            'equipment_id' => $activeLog->equipment_id,
            'status' => $activeLog->status,
            'started_at' => $activeLog->started_at,
            'end_use_at' => $activeLog->end_use_at,
            'actual_end_at' => $activeLog->actual_end_at,
            'personnel' => $activeLog->personnel ? [
                'id' => $activeLog->personnel->id,
                'employee_id' => $activeLog->personnel->employee_id,
                'fname' => $activeLog->personnel->fname,
                'mname' => $activeLog->personnel->mname,
                'lname' => $activeLog->personnel->lname,
                'suffix' => $activeLog->personnel->suffix,
            ] : null,
        ];
    }

    private function sendOverdueEmailNotification(LaboratoryEquipmentLog $log, string $equipmentType): void
    {
        $recipient = trim((string) ($log->personnel?->email ?? ''));

        if ($recipient === '') {
            return;
        }

        try {
            Mail::to($recipient)->send(new LaboratoryEquipmentLogOverdueMail(
                $log->fresh(['personnel', 'equipment']),
                $equipmentType,
            ));
        } catch (\Throwable $exception) {
            Log::warning('Failed to send overdue equipment log email.', [
                'log_id' => $log->id,
                'equipment_id' => $log->equipment_id,
                'recipient' => $recipient,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
