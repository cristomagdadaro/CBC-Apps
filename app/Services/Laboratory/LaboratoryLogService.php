<?php

namespace App\Services\Laboratory;

use App\Enums\Inventory;
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
use Illuminate\Pagination\LengthAwarePaginator;
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
        $query = Item::query()
            ->with('category')
            ->select([
                'items.id as equipment_id',
                'items.name',
                'items.brand',
                'items.description',
                'items.category_id',
                'categories.name as category_name',
            ])
            ->selectSub($this->latestIncomingTransactionFieldSubquery('equipment_logger_mode'), 'equipment_logger_mode')
            ->selectSub($this->latestTransactionFieldSubquery('barcode'), 'barcode')
            ->selectSub($this->latestTransactionFieldSubquery('barcode_prri'), 'barcode_prri')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->where(function (Builder $query) use ($equipmentType) {
                $this->applyEquipmentCategoryConstraint($query, $equipmentType, 'categories');
            });

        $this->applyLatestIncomingModeConstraint($query, Transaction::EQUIPMENT_LOGGER_MODE_BORROWABLE);

        if ($search) {
            $query->where(function (Builder $query) use ($search) {
                $query->where('items.id', 'like', "%{$search}%")
                    ->orWhere('items.name', 'like', "%{$search}%")
                    ->orWhere('items.brand', 'like', "%{$search}%")
                    ->orWhereExists(function ($subQuery) use ($search) {
                        $subQuery->selectRaw('1')
                            ->from('transactions')
                            ->whereColumn('transactions.item_id', 'items.id')
                            ->where(function ($barcodeQuery) use ($search) {
                                $barcodeQuery->where('transactions.barcode', 'like', "%{$search}%")
                                    ->orWhere('transactions.barcode_prri', 'like', "%{$search}%");
                            });
                    });
            });
        }

        return $query
            ->orderBy('items.name')
            ->get();
    }

    public function getEquipmentDetails(string $equipmentId, string $equipmentType = 'laboratory'): array
    {
        $equipment = $this->requireEligibleEquipment($equipmentId, $equipmentType);

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
        $equipment = $this->requireEligibleEquipment($equipmentId, $equipmentType);

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
        $equipment = $this->requireEligibleEquipment($equipmentId, $equipmentType);

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
        $equipment = $this->requireEligibleEquipment($equipmentId, $equipmentType);

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
        $equipment = $this->requireEligibleEquipment($equipmentId, $equipmentType);

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
        $query = LaboratoryEquipmentLog::with(['equipment', 'personnel'])
            ->whereIn('status', ['active', 'overdue'])
            ->whereHas('equipment', function (Builder $builder) use ($equipmentType) {
                $builder->whereHas('transactions', function (Builder $transactionQuery) {
                    $transactionQuery->withTrashed()
                        ->where('transactions.transac_type', Inventory::INCOMING->value)
                        ->whereIn('transactions.equipment_logger_mode', [
                            Transaction::EQUIPMENT_LOGGER_MODE_BORROWABLE,
                            Transaction::EQUIPMENT_LOGGER_MODE_TRACKED_ONLY,
                        ]);
                })->whereHas('category', function (Builder $categoryQuery) use ($equipmentType) {
                    $this->applyEquipmentCategoryConstraint($categoryQuery, $equipmentType, 'categories');
                });
            })
            ->orderBy('started_at');

        if ($employee_id) {
            $query->whereHas('personnel', function ($q) use ($employee_id) {
                $q->where('employee_id', $employee_id);
            });
        }

        return $query->get();
    }

    public function getDashboardMetrics(string $equipmentType = 'all'): array
    {
        $activeLogs = $this->getDashboardLogsByStatuses(['active'], $equipmentType);

        $overdueLogs = $this->getDashboardLogsByStatuses(['overdue'], $equipmentType);
        $completedLogs = $this->getDashboardLogsByStatuses(['completed'], $equipmentType);

        $this->enrichLogsWithLocationDetails($activeLogs, $overdueLogs, $completedLogs);

        $mostUsedRows = LaboratoryEquipmentLog::query()
            ->select([
                'equipment_id',
                DB::raw('COUNT(*) as usage_count'),
                DB::raw($this->totalDurationExpression()),
            ])
            ->whereHas('equipment', function (Builder $builder) use ($equipmentType) {
                $builder->whereHas('transactions', function (Builder $transactionQuery) {
                    $transactionQuery->withTrashed()
                        ->where('transactions.transac_type', Inventory::INCOMING->value)
                        ->whereIn('transactions.equipment_logger_mode', [
                            Transaction::EQUIPMENT_LOGGER_MODE_BORROWABLE,
                            Transaction::EQUIPMENT_LOGGER_MODE_TRACKED_ONLY,
                        ]);
                })->whereHas('category', function (Builder $categoryQuery) use ($equipmentType) {
                    $this->applyEquipmentCategoryConstraint($categoryQuery, $equipmentType, 'categories');
                });
            })
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
                'equipment_type' => $this->determineEquipmentType($equipment),
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
            ->whereHas('equipment', function (Builder $builder) use ($equipmentType) {
                $builder->whereHas('transactions', function (Builder $transactionQuery) {
                    $transactionQuery->withTrashed()
                        ->where('transactions.transac_type', Inventory::INCOMING->value)
                        ->whereIn('transactions.equipment_logger_mode', [
                            Transaction::EQUIPMENT_LOGGER_MODE_BORROWABLE,
                            Transaction::EQUIPMENT_LOGGER_MODE_TRACKED_ONLY,
                        ]);
                })->whereHas('category', function (Builder $categoryQuery) use ($equipmentType) {
                    $this->applyEquipmentCategoryConstraint($categoryQuery, $equipmentType, 'categories');
                });
            })
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

    public function paginateEquipmentUsage(array $parameters, string $equipmentType = 'all'): LengthAwarePaginator
    {
        $search = trim((string) ($parameters['search'] ?? ''));
        $filter = trim((string) ($parameters['filter'] ?? ''));
        $perPage = max(1, min(100, (int) ($parameters['per_page'] ?? 10)));
        $sort = (string) ($parameters['sort'] ?? 'total_logs');
        $order = strtolower((string) ($parameters['order'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';

        $logSummarySubquery = LaboratoryEquipmentLog::query()
            ->selectRaw('equipment_id')
            ->selectRaw('COUNT(*) as total_logs')
            ->selectRaw("SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_logs")
            ->selectRaw("SUM(CASE WHEN status = 'overdue' THEN 1 ELSE 0 END) as overdue_logs")
            ->selectRaw("SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_logs")
            ->selectRaw('MAX(COALESCE(actual_end_at, end_use_at, started_at)) as last_logged_at')
            ->groupBy('equipment_id');

        $query = Item::query()
            ->select([
                'items.id',
                'items.name',
                'items.brand',
                'items.description',
                'items.category_id',
                'categories.name as category_name',
            ])
            ->selectSub($this->latestIncomingTransactionFieldSubquery('equipment_logger_mode'), 'equipment_logger_mode')
            ->selectRaw("CASE WHEN items.category_id = 4 THEN 'ict' ELSE 'laboratory' END as equipment_type")
            ->selectRaw('COALESCE(logger_usage.total_logs, 0) as total_logs')
            ->selectRaw('COALESCE(logger_usage.active_logs, 0) as active_logs')
            ->selectRaw('COALESCE(logger_usage.overdue_logs, 0) as overdue_logs')
            ->selectRaw('COALESCE(logger_usage.completed_logs, 0) as completed_logs')
            ->selectRaw('logger_usage.last_logged_at as last_logged_at')
            ->selectSub($this->latestTransactionFieldSubquery('barcode'), 'barcode')
            ->leftJoinSub($logSummarySubquery, 'logger_usage', function ($join) {
                $join->on('logger_usage.equipment_id', '=', 'items.id');
            })
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->whereHas('transactions', function (Builder $query) {
                $query->withTrashed()
                    ->where('transactions.transac_type', Inventory::INCOMING->value)
                    ->whereIn('transactions.equipment_logger_mode', [
                        Transaction::EQUIPMENT_LOGGER_MODE_BORROWABLE,
                        Transaction::EQUIPMENT_LOGGER_MODE_TRACKED_ONLY,
                    ]);
            })
            ->where(function (Builder $query) use ($equipmentType) {
                $this->applyEquipmentCategoryConstraint($query, $equipmentType, 'categories');
            });

        if ($search !== '') {
            if ($filter !== '') {
                $this->applyEquipmentUsageFilterSearch($query, $filter, $search);
            } else {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('items.name', 'like', "%{$search}%")
                        ->orWhere('items.brand', 'like', "%{$search}%")
                        ->orWhere('items.description', 'like', "%{$search}%")
                        ->orWhere('categories.name', 'like', "%{$search}%")
                        ->orWhereExists(function ($subQuery) use ($search) {
                            $subQuery->selectRaw('1')
                                ->from('transactions')
                                ->whereColumn('transactions.item_id', 'items.id')
                                ->where('transactions.transac_type', Inventory::INCOMING->value)
                                ->where('transactions.equipment_logger_mode', 'like', "%{$search}%");
                        })
                        ->orWhereExists(function ($subQuery) use ($search) {
                            $subQuery->selectRaw('1')
                                ->from('transactions')
                                ->whereColumn('transactions.item_id', 'items.id')
                                ->where(function ($barcodeQuery) use ($search) {
                                    $barcodeQuery->where('transactions.barcode', 'like', "%{$search}%")
                                        ->orWhere('transactions.barcode_prri', 'like', "%{$search}%");
                                });
                        });
                });
            }
        }

        $sortableColumns = [
            'name' => 'items.name',
            'brand' => 'items.brand',
            'category_name' => 'categories.name',
            'equipment_logger_mode' => 'equipment_logger_mode',
            'total_logs' => 'total_logs',
            'active_logs' => 'active_logs',
            'overdue_logs' => 'overdue_logs',
            'completed_logs' => 'completed_logs',
            'last_logged_at' => 'last_logged_at',
        ];

        $query->orderBy($sortableColumns[$sort] ?? 'total_logs', $order)
            ->orderBy('items.name');

        return $query->paginate($perPage);
    }

    private function applyEquipmentUsageFilterSearch(Builder $query, string $filter, string $search): void
    {
        $likeValue = "%{$search}%";

        match ($filter) {
            'name' => $query->where('items.name', 'like', $likeValue),
            'category_name' => $query->where('categories.name', 'like', $likeValue),
            'equipment_logger_mode' => $query->whereExists(function ($subQuery) use ($likeValue) {
                $subQuery->selectRaw('1')
                    ->from('transactions')
                    ->whereColumn('transactions.item_id', 'items.id')
                    ->where('transactions.transac_type', Inventory::INCOMING->value)
                    ->where('transactions.equipment_logger_mode', 'like', $likeValue);
            }),
            'equipment_type' => $query->where('items.category_id', strtolower($search) === 'ict' ? 4 : 7),
            'barcode' => $query->whereExists(function ($subQuery) use ($likeValue) {
                $subQuery->selectRaw('1')
                    ->from('transactions')
                    ->whereColumn('transactions.item_id', 'items.id')
                    ->where(function ($barcodeQuery) use ($likeValue) {
                        $barcodeQuery->where('transactions.barcode', 'like', $likeValue)
                            ->orWhere('transactions.barcode_prri', 'like', $likeValue);
                    });
            }),
            default => $query->where('items.name', 'like', $likeValue),
        };
    }

    private function getDashboardLogsByStatuses(array $statuses, string $equipmentType = 'laboratory'): Collection
    {
        return LaboratoryEquipmentLog::query()
            ->with(['equipment', 'personnel'])
            ->whereIn('status', $statuses)
            ->whereHas('equipment', function (Builder $builder) use ($equipmentType) {
                $builder->whereHas('transactions', function (Builder $transactionQuery) {
                    $transactionQuery->withTrashed()
                        ->where('transactions.transac_type', Inventory::INCOMING->value)
                        ->whereIn('transactions.equipment_logger_mode', [
                            Transaction::EQUIPMENT_LOGGER_MODE_BORROWABLE,
                            Transaction::EQUIPMENT_LOGGER_MODE_TRACKED_ONLY,
                        ]);
                })->whereHas('category', function (Builder $categoryQuery) use ($equipmentType) {
                    $this->applyEquipmentCategoryConstraint($categoryQuery, $equipmentType, 'categories');
                });
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
            $log->setAttribute('equipment_type', $this->determineEquipmentType($log->equipment));
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
        return Item::query()
            ->with('category')
            ->select('items.*')
            ->addSelect(
                DB::raw('(SELECT t.equipment_logger_mode FROM transactions t WHERE t.item_id = items.id AND t.transac_type = "incoming" AND t.equipment_logger_mode IS NOT NULL ORDER BY t.created_at DESC LIMIT 1) as equipment_logger_mode'),
                DB::raw('(SELECT t.barcode FROM transactions t WHERE t.item_id = items.id AND t.barcode IS NOT NULL ORDER BY t.created_at DESC LIMIT 1) as barcode'),
                DB::raw('(SELECT t.barcode_prri FROM transactions t WHERE t.item_id = items.id AND t.barcode_prri IS NOT NULL ORDER BY t.created_at DESC LIMIT 1) as barcode_prri')
            )
            ->where('items.id', $equipmentId)
            ->whereHas('category', function (Builder $query) use ($equipmentType) {
                $this->applyEquipmentCategoryConstraint($query, $equipmentType, 'categories');
            })
            ->where(function (Builder $query) {
                $this->applyLatestIncomingModeConstraint($query, Transaction::EQUIPMENT_LOGGER_MODE_BORROWABLE);
            })
            ->first();
    }

    private function requireEligibleEquipment(string $equipmentId, string $equipmentType = 'laboratory'): Item
    {
        $equipment = $this->findEligibleEquipment($equipmentId, $equipmentType);

        if ($equipment) {
            return $equipment;
        }

        $item = $this->findEquipmentForValidation($equipmentId);
        if ($item && ! $this->itemMatchesEquipmentType($item, $equipmentType)) {
            $categoryName = $item->category?->name ?? 'Unknown Category';
            abort(422, "Sorry, this is a {$categoryName} item. You can only log {$this->equipmentLabel($equipmentType)}.");
        }

        $latestMode = $this->getLatestIncomingEquipmentLoggerMode($equipmentId);
        $modeMessage = $this->equipmentLoggerModeUnavailableMessage($latestMode);

        if ($modeMessage !== null) {
            abort(422, $modeMessage);
        }

        abort(404, 'Equipment not found.');
    }

    private function categoryIdsForType(string $equipmentType): array
    {
        return match ($equipmentType) {
            'ict' => [4],
            'all' => [4, 7],
            default => [7],
        };
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

    private function itemMatchesEquipmentType(Item $item, string $equipmentType): bool
    {
        return in_array((int) $item->category_id, $this->categoryIdsForType($equipmentType), true)
            || ($equipmentType === 'laboratory' && strtolower((string) ($item->category?->name ?? '')) === 'laboratory equipment');
    }

    private function applyEquipmentCategoryConstraint(Builder $query, string $equipmentType, string $table = 'categories'): void
    {
        $query->whereIn(sprintf('%s.id', $table), $this->categoryIdsForType($equipmentType));

        if ($equipmentType === 'laboratory') {
            $query->orWhere(sprintf('%s.name', $table), 'Laboratory Equipment');
        }
    }

    private function latestTransactionFieldSubquery(string $field): Builder
    {
        return Transaction::withTrashed()
            ->select($field)
            ->whereColumn('transactions.item_id', 'items.id')
            ->whereNotNull($field)
            ->latest('created_at')
            ->limit(1);
    }

    private function latestIncomingTransactionFieldSubquery(string $field): Builder
    {
        return Transaction::withTrashed()
            ->select($field)
            ->whereColumn('transactions.item_id', 'items.id')
            ->where('transactions.transac_type', Inventory::INCOMING->value)
            ->whereNotNull($field)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(1);
    }

    private function applyLatestIncomingModeConstraint(Builder $query, string $requiredMode): void
    {
        $query->whereExists(function ($subQuery) use ($requiredMode) {
            $subQuery->selectRaw('1')
                ->from('transactions as latest_incoming')
                ->whereColumn('latest_incoming.item_id', 'items.id')
                ->where('latest_incoming.transac_type', Inventory::INCOMING->value)
                ->whereNotNull('latest_incoming.equipment_logger_mode')
                ->where('latest_incoming.equipment_logger_mode', $requiredMode)
                ->whereRaw(
                    'latest_incoming.id = (
                        select t_latest.id
                        from transactions as t_latest
                        where t_latest.item_id = items.id
                          and t_latest.transac_type = ?
                          and t_latest.equipment_logger_mode is not null
                        order by t_latest.created_at desc, t_latest.id desc
                        limit 1
                    )',
                    [Inventory::INCOMING->value],
                );
        });
    }

    private function getLatestIncomingEquipmentLoggerMode(string $equipmentId): ?string
    {
        return Transaction::withTrashed()
            ->where('item_id', $equipmentId)
            ->where('transac_type', Inventory::INCOMING->value)
            ->whereNotNull('equipment_logger_mode')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->value('equipment_logger_mode');
    }

    private function equipmentLoggerModeUnavailableMessage(?string $mode): ?string
    {
        $label = $mode ? $this->equipmentLoggerModeLabel($mode) : null;

        return match ($mode) {
            Transaction::EQUIPMENT_LOGGER_MODE_TRACKED_ONLY => sprintf(
                'This equipment exists, but its latest incoming stock is marked as "%s" and is not available in the borrowable equipment logger flow.',
                $label,
            ),
            Transaction::EQUIPMENT_LOGGER_MODE_EXCLUDED => sprintf(
                'This equipment exists, but its latest incoming stock is marked as "%s" and is excluded from the equipment logger flow.',
                $label,
            ),
            default => null,
        };
    }

    private function equipmentLoggerModeLabel(string $mode): string
    {
        $labels = collect($this->optionRepo->getEquipmentLoggerModeOptions())
            ->mapWithKeys(function (array $option) {
                $name = $option['name'] ?? null;
                $label = $option['label'] ?? null;

                return $name && $label ? [$name => $label] : [];
            });

        return $labels->get($mode, str($mode)->replace('_', ' ')->headline());
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
