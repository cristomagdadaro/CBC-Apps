<?php

namespace App\Repositories;

use App\Enums\Inventory;
use App\Models\LaboratoryEquipmentLocationSurvey;
use App\Models\Transaction;
use App\Models\Category;
use App\Repositories\OptionRepo;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use App\Pipelines\InventoryTransaction\ResolveUserByEmployeeId;
use App\Pipelines\InventoryTransaction\AssignTransactionUuid;
use App\Pipelines\InventoryTransaction\PersistTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TransactionRepo extends AbstractRepoService
{
    public function __construct(Transaction $model, private readonly OptionRepo $optionRepo)
    {
        parent::__construct($model);
        $this->appendWith = ['item', 'user','personnel'];
    }

    public function create(array $data)
    {
        $components = collect($data['components'] ?? [])
            ->filter(fn ($component) => !empty($component['item_id']) && !empty($component['quantity']))
            ->values();

        unset($data['components']);

        return DB::transaction(function () use ($data, $components) {
            $main = $this->model->newQuery()->create($data);

            if (($data['transac_type'] ?? null) === 'incoming' && $components->isNotEmpty()) {
                $components->each(function (array $component) use ($main, $data) {
                    $quantity = (float) ($component['quantity'] ?? 0);
                    $prriComponentNo = $component['prri_component_no'] ?? null;

                    $main->components()->create([
                        'transaction_id' => $main->id,
                        'item_id' => $component['item_id'],
                        'quantity' => $quantity,
                        'unit' => $component['unit'] ?? ($data['unit'] ?? null),
                        'barcode_prri' => $component['barcode_prri'] ?? ($data['barcode_prri'] ?? null),
                        'prri_component_no' => $prriComponentNo !== null && $prriComponentNo !== ''
                            ? str_pad((string) ((int) $prriComponentNo), 5, '0', STR_PAD_LEFT)
                            : null,
                        'expiration' => $component['expiration'] ?? ($data['expiration'] ?? null),
                        'remarks' => $component['remarks'] ?? null,
                    ]);
                });
            }

            return $main;
        });
    }

    public function delete(int|string $id): Model
    {
        return DB::transaction(function () use ($id) {
            $model = $this->model->newQuery()->findOrFail($id);
            $deletedData = $model->getAttributes();

            $model->components()->delete();
            $model->reports()->delete();
            $model->delete();

            $model->setRawAttributes($deletedData);

            return $model;
        });
    }

    public function forceDelete(int|string $id): Model
    {
        return DB::transaction(function () use ($id) {
            $model = $this->model->newQuery()->withTrashed()->findOrFail($id);
            $deletedData = $model->getAttributes();

            $model->components()->withTrashed()->forceDelete();
            $model->reports()->withTrashed()->forceDelete();
            $model->forceDelete();

            $model->setRawAttributes($deletedData);

            return $model;
        });
    }

    public function update(int|string $id, array $data): Model
    {
        $components = collect($data['components'] ?? [])
            ->filter(fn ($component) => !empty($component['item_id']) && !empty($component['quantity']))
            ->values();

        unset($data['components']);

        return DB::transaction(function () use ($id, $data, $components) {
            $model = $this->model->newQuery()->findOrFail($id);
            $model->fill($data);
            $model->save();

            if (($model->transac_type ?? null) !== 'incoming') {
                return $model;
            }

            $model->components()->delete();

            if ($components->isEmpty()) {
                return $model;
            }

            $components->each(function (array $component) use ($model) {
                $quantity = (float) ($component['quantity'] ?? 0);
                $prriComponentNo = $component['prri_component_no'] ?? null;

                $model->components()->create([
                    'transaction_id' => $model->id,
                    'item_id' => $component['item_id'],
                    'quantity' => $quantity,
                    'unit' => $component['unit'] ?? ($model->unit ?? null),
                    'barcode_prri' => $component['barcode_prri'] ?? ($model->barcode_prri ?? null),
                    'prri_component_no' => $prriComponentNo !== null && $prriComponentNo !== ''
                        ? str_pad((string) ((int) $prriComponentNo), 5, '0', STR_PAD_LEFT)
                        : null,
                    'expiration' => $component['expiration'] ?? ($model->expiration ?? null),
                    'remarks' => $component['remarks'] ?? null,
                ]);
            });

            return $model;
        });
    }

    public function getRemainingStocks(Collection $parameters, array $consumableCategoryIds = [1,2,3,5,6,11,12]): Collection
    {
        $rawSearch = $parameters->get('search');
        $searchTerm = $rawSearch !== null ? trim((string) $rawSearch) : '';
        $hasSearchTerm = $searchTerm !== '';
        $isExact  = filter_var($parameters->get('is_exact', false), FILTER_VALIDATE_BOOLEAN);
        $sort     = $parameters->get('sort', 'id');
        $order    = strtolower($parameters->get('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage  = $parameters->get('per_page', '*');
        $page     = (int) $parameters->get('page', 1);
        $paginate = filter_var($parameters->get('paginate', true), FILTER_VALIDATE_BOOLEAN);
        $filter    = $parameters->get('filter');
        $filterBy  = $parameters->get('filter_by');
        $minRemaining = $parameters->get('min_remaining');
        $includeAllCategories = filter_var($parameters->get('include_all_categories', false), FILTER_VALIDATE_BOOLEAN);
        $storageLocationId = $parameters->get('storage_location_id');
        $storageLocationCode = $this->normalizeLocationCode($storageLocationId);

        $orderByRaw = match ($sort) {
            'name'               => 'items.name',
            'brand'              => 'items.brand',
            'unit'               => 'transactions.unit',
            'barcode'            => 'transactions.barcode',
            'barcode_prri'       => 'transactions.barcode_prri',
            'total_ingoing'      => 'total_ingoing',
            'total_outgoing'     => 'total_outgoing',
            'remaining_quantity' => 'remaining_quantity',
            'expiration'         => 'expiration',
            default              => 'items.id',
        };

        $query = $this->model->newQuery()->selectRaw(
            'items.name, items.description, items.brand, transactions.unit, items.id as item_id, transactions.barcode, transactions.barcode_prri, MAX(transactions.project_code) as project_code,' .
                ' SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) as total_ingoing,' .
                ' SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END) as total_outgoing,' .
                ' (SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) - ' .
                '  SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END)) as remaining_quantity,' .
                ' MIN(transactions.expiration) as expiration,' .
                ' CASE ' .
                '   WHEN MIN(transactions.expiration) IS NULL THEN 0 ' .
                '   WHEN MIN(transactions.expiration) < CURDATE() THEN 3 ' .
                '   WHEN MIN(transactions.expiration) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 2 ' .
                '   ELSE 1 ' .
                ' END as expiration_priority'
            )->join('items', 'transactions.item_id', '=', 'items.id')
            ->groupBy('items.id', 'items.name', 'items.description', 'items.brand', 'transactions.unit', 'transactions.barcode', 'transactions.barcode_prri');

        if ($filter === 'category' && $filterBy) {
            $values = is_array($filterBy) ? $filterBy : [$filterBy];

            $ids = [];
            $names = [];

            foreach ($values as $value) {
                if (is_numeric($value)) {
                    $ids[] = (int) $value;
                } else {
                    $names[] = trim($value);
                }
            }

            if (!empty($names)) {
                $catIds = Category::where(function ($q) use ($names) {
                        $q->whereIn('name', $names);

                        foreach ($names as $name) {
                            $q->orWhere('name', 'like', "%{$name}%");
                        }
                    })
                    ->pluck('id')
                    ->toArray();

                $ids = array_merge($ids, $catIds);
            }

            $ids = array_values(array_unique($ids));

            if (!empty($ids)) {
                $query->whereIn('items.category_id', $ids);
            } else {
                $query->where(function ($q) use ($names) {
                    foreach ($names as $name) {
                        $like = "%{$name}%";
                        $q->orWhere('items.name', 'like', $like)
                            ->orWhere('items.description', 'like', $like)
                            ->orWhere('items.brand', 'like', $like);
                    }
                });
            }
        } elseif (!$filter && !$includeAllCategories && !empty($consumableCategoryIds)) {
            $query->whereIn('items.category_id', $consumableCategoryIds);
        } elseif ($filter === 'quantity' && $filterBy) {
            $percentageExpr = 'CASE WHEN total_ingoing <> 0 THEN remaining_quantity / total_ingoing ELSE 0 END';

            switch ($filterBy) {
                case 'empty':
                    $query->havingRaw("$percentageExpr <= 0");
                    break;
                case 'low':
                    $query->havingRaw("$percentageExpr > 0 AND $percentageExpr <= 0.25");
                    break;
                case 'mid':
                    $query->havingRaw("$percentageExpr > 0.25 AND $percentageExpr <= 0.75");
                    break;
                case 'high':
                    $query->havingRaw("$percentageExpr > 0.75");
                    break;
            }
        } elseif ($filter === 'barcode') {
            if ($hasSearchTerm) {
                $like = '%' . $searchTerm . '%';
                $query->havingRaw('barcode LIKE ?', [$like]);
            }
        } elseif ($filter === 'project_code' && $filterBy) {
            $query->where('transactions.project_code', $filterBy);
        }

        if ($storageLocationCode !== null) {
            $query->where('transactions.barcode', 'like', "CBC-{$storageLocationCode}-%");
        }

        if ($minRemaining !== null && is_numeric($minRemaining)) {
            $query->havingRaw('remaining_quantity >= ?', [(float) $minRemaining]);
        }

        if ($hasSearchTerm) {
            if ($isExact) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('items.name', $searchTerm)
                        ->orWhere('items.brand', $searchTerm)
                        ->orWhere('items.description', $searchTerm)
                        ->orWhere('transactions.unit', $searchTerm)
                        ->orWhere('transactions.barcode', $searchTerm)
                        ->orWhere('transactions.barcode_prri', $searchTerm)
                        ->orWhere('transactions.project_code', $searchTerm)
                        ->orWhere('transactions.transac_type', $searchTerm)
                        ->orWhere('transactions.remarks', $searchTerm);
                });

                if (is_numeric($searchTerm)) {
                    $query->havingRaw(
                        'total_outgoing = ? OR total_ingoing = ? OR remaining_quantity = ?',
                        [$searchTerm, $searchTerm, $searchTerm]
                    );
                }
            } else {
                $like = '%' . $searchTerm . '%';

                $query->where(function ($q) use ($like) {
                    $q->where('items.name', 'like', $like)
                        ->orWhere('items.brand', 'like', $like)
                        ->orWhere('items.description', 'like', $like)
                        ->orWhere('transactions.unit', 'like', $like)
                        ->orWhere('transactions.barcode', 'like', $like)
                        ->orWhere('transactions.barcode_prri', 'like', $like)
                        ->orWhere('transactions.project_code', 'like', $like)
                        ->orWhere('transactions.transac_type', 'like', $like)
                        ->orWhere('transactions.remarks', 'like', $like);
                });

                if (is_numeric($searchTerm)) {
                    $query->orHavingRaw(
                        'CAST(total_outgoing AS CHAR) LIKE ? OR CAST(total_ingoing AS CHAR) LIKE ? OR CAST(remaining_quantity AS CHAR) LIKE ?',
                        [$like, $like, $like]
                    );
                }
            }
        }

        // When sorting by name, order by expiration priority first, then name A-Z
        // Otherwise, apply the standard orderByRaw
        if ($sort === 'name') {
            $query->orderByRaw('expiration_priority ASC')
                  ->orderByRaw('items.name ' . $order);
        } else {
            $query->orderByRaw($orderByRaw . ' ' . $order);
        }

        if ($paginate && $perPage !== '*') {
            $data = $query->paginate($perPage, ['*'], 'page', $page);
        } else {
            $data = ['data' => $query->get()];
        }

        return new Collection($data);
    }

    public function applySorting(Builder &$query, Collection $parameters): void
    {
        $sortColumn = $parameters->get('sort');

        if (!$sortColumn) {
            $query->orderBy('transactions.created_at', 'desc');
            return;
        }

        parent::applySorting($query, $parameters);
    }

    public function createOutgoingWithPipeline(array $validated): Model
    {
        $context = app(Pipeline::class)
            ->send([
                'repo' => $this,
                'payload' => $validated,
                'model' => null,
            ])
            ->through([
                ResolveUserByEmployeeId::class,
                AssignTransactionUuid::class,
                PersistTransaction::class,
            ])
            ->thenReturn();

        return $context['model'];
    }

    public function findRecountingCandidateByBarcode(string $barcode): ?array
    {
        $needle = trim($barcode);

        if ($needle === '') {
            return null;
        }

        $summary = $this->model->newQuery()
            ->selectRaw(
                'items.id as item_id,
                items.name,
                items.brand,
                items.description,
                transactions.barcode,
                transactions.barcode_prri,
                transactions.unit,
                MAX(transactions.project_code) as project_code,
                SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) as total_incoming,
                SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END) as total_outgoing,
                (SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END)
                  - SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END)) as remaining_quantity,
                MAX(transactions.created_at) as latest_transaction_at'
            )
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->where(function (Builder $query) use ($needle) {
                $query->where('transactions.barcode', $needle)
                    ->orWhere('transactions.barcode_prri', $needle);
            })
            ->groupBy(
                'items.id',
                'items.name',
                'items.brand',
                'items.description',
                'transactions.barcode',
                'transactions.barcode_prri',
                'transactions.unit'
            )
            ->orderByDesc('latest_transaction_at')
            ->first();

        if (!$summary) {
            return null;
        }

        $locationSurvey = LaboratoryEquipmentLocationSurvey::query()
            ->where('equipment_id', $summary->item_id)
            ->first();

        return [
            'item_id' => (string) $summary->item_id,
            'name' => (string) ($summary->name ?? ''),
            'brand' => (string) ($summary->brand ?? ''),
            'description' => (string) ($summary->description ?? ''),
            'barcode' => (string) ($summary->barcode ?? ''),
            'barcode_prri' => (string) ($summary->barcode_prri ?? ''),
            'unit' => (string) ($summary->unit ?? ''),
            'project_code' => (string) ($summary->project_code ?? ''),
            'total_incoming' => (int) ($summary->total_incoming ?? 0),
            'total_outgoing' => (int) ($summary->total_outgoing ?? 0),
            'remaining_quantity' => (int) ($summary->remaining_quantity ?? 0),
            'latest_transaction_at' => $summary->latest_transaction_at,
            'location' => $locationSurvey
                ? [
                    'location_code' => $locationSurvey->location_code,
                    'location_label' => $locationSurvey->location_label,
                    'reported_at' => $locationSurvey->reported_at,
                ]
                : null,
        ];
    }

    public function applyInventoryRecountAdjustment(array $payload, ?string $userId = null): array
    {
        $candidate = $this->findRecountingCandidateByBarcode((string) ($payload['barcode'] ?? ''));

        if (!$candidate) {
            throw ValidationException::withMessages([
                'barcode' => 'No inventory item matched the scanned barcode.',
            ]);
        }

        $systemCount = (int) ($candidate['remaining_quantity'] ?? 0);
        $physicalCount = (int) ($payload['physical_count'] ?? 0);
        $adjustment = $physicalCount - $systemCount;

        return DB::transaction(function () use ($payload, $candidate, $systemCount, $physicalCount, $adjustment, $userId) {
            $locationUpdated = false;

            $locationCode = isset($payload['location_code'])
                ? trim((string) $payload['location_code'])
                : null;
            $locationLabel = isset($payload['location_label'])
                ? trim((string) $payload['location_label'])
                : null;

            if (($locationCode && $locationLabel) || (!$locationCode && $locationLabel)) {
                LaboratoryEquipmentLocationSurvey::query()->updateOrCreate(
                    ['equipment_id' => $candidate['item_id']],
                    [
                        'personnel_id' => null,
                        'location_code' => $locationCode ?: null,
                        'location_label' => $locationLabel,
                        'reported_at' => Carbon::now(),
                    ]
                );

                $locationUpdated = true;
            }

            $transaction = null;

            if ($adjustment !== 0) {
                $transacType = $adjustment > 0
                    ? Inventory::INCOMING->value
                    : Inventory::OUTGOING->value;

                $quantity = abs($adjustment);
                $resolvedUserId = $userId ?: Auth::id();

                $remarks = sprintf(
                    'Inventory adjustment via recounting. System count: %d, Physical count: %d, Adjustment: %s%d.',
                    $systemCount,
                    $physicalCount,
                    $adjustment > 0 ? '+' : '-',
                    $quantity,
                );

                if ($locationUpdated && !empty($locationLabel)) {
                    $remarks .= sprintf(' Location updated to: %s%s.', $locationCode ? "{$locationCode} - " : '', $locationLabel);
                }

                $transaction = $this->model->newQuery()->create([
                    'item_id' => $candidate['item_id'],
                    'barcode' => $candidate['barcode'] ?: ($payload['barcode'] ?? null),
                    'barcode_prri' => $candidate['barcode_prri'] ?: null,
                    'transac_type' => $transacType,
                    'quantity' => $quantity,
                    'unit' => $candidate['unit'] ?: null,
                    'project_code' => $candidate['project_code'] ?: null,
                    'user_id' => $resolvedUserId,
                    'remarks' => $remarks,
                ]);
            }

            return [
                'item' => $this->findRecountingCandidateByBarcode((string) ($payload['barcode'] ?? '')),
                'system_count' => $systemCount,
                'physical_count' => $physicalCount,
                'adjustment' => $adjustment,
                'transaction_created' => $adjustment !== 0,
                'location_updated' => $locationUpdated,
                'transaction' => $transaction,
            ];
        });
    }

    public function getRemainingStocksPerCategory(Collection $parameters, string $categoryName): Collection
    {
        $minRemaining = $parameters->get('min_remaining', 1);

        $params = $parameters->merge([
            'filter' => 'category',
            'filter_by' => $categoryName,
            'min_remaining' => $minRemaining,
        ]);

        $stock = $this->getRemainingStocks($params);

        return collect($stock->get('data', []))
            ->map(function ($row) {

                $baseLabel = trim(
                    $row->name .
                    ($row->brand ? " - {$row->brand}" : '') .
                    ($row->description ? " ({$row->description})" : '')
                );

                $stockInfo = $row->remaining_quantity !== null
                    ? " - {$row->remaining_quantity}" . ($row->unit ? " {$row->unit} remaining" : '')
                    : '';

                return [
                    'value' => $row->item_id,
                    'label' => $baseLabel . $stockInfo,
                    'barcode' => $row->barcode,
                    'barcode_prri' => $row->barcode_prri ?? null,
                    'unit' => $row->unit,
                    'expiration' => $row->expiration,
                    'remaining_quantity' => (int) $row->remaining_quantity,
                ];
            })
            ->values();
    }

    public function getRecentTransactions(int $limit = 5): Collection
    {
        return $this->model
            ->newQuery()
            ->with(['item', 'personnel'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getAvailableProjectCodes(): Collection
    {
        return $this->model
            ->newQuery()
            ->whereNotNull('project_code')
            ->where('project_code', '!=', '')
            ->select('project_code as name', 'project_code as label')
            ->distinct()
            ->orderBy('project_code')
            ->get();
    }

    public function getInventoryDashboardMetrics(Collection $parameters): array
    {
        $scope = strtolower((string) $parameters->get('scope', 'monthly'));
        [$start, $end] = $this->resolveDashboardDateRange($scope, $parameters);

        $base = $this->model->newQuery()
            ->when($start && $end, function (Builder $query) use ($start, $end) {
                $query->whereBetween('transactions.created_at', [$start, $end]);
            });

        $totalIncoming = (clone $base)
            ->where('transactions.transac_type', 'incoming')
            ->sum('transactions.quantity');

        $totalOutgoing = (clone $base)
            ->where('transactions.transac_type', 'outgoing')
            ->sum(DB::raw('ABS(transactions.quantity)'));

        $recentTransactions = (clone $base)
            ->with(['item', 'personnel', 'user'])
            ->orderByDesc('transactions.created_at')
            ->limit(10)
            ->get();

        $itemsPerCategory = (clone $base)
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as label, COUNT(DISTINCT transactions.item_id) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();

        $itemsPerProjectCode = (clone $base)
            ->whereNotNull('transactions.project_code')
            ->where('transactions.project_code', '!=', '')
            ->selectRaw('transactions.project_code as label, COUNT(DISTINCT transactions.item_id) as total')
            ->groupBy('transactions.project_code')
            ->orderByDesc('total')
            ->get();

        $latestBarcodeRows = (clone $base)
            ->whereNotNull('transactions.item_id')
            ->whereNotNull('transactions.barcode')
            ->select(['transactions.item_id', 'transactions.barcode'])
            ->orderByDesc('transactions.created_at')
            ->get()
            ->unique('item_id');

        $locationLookup = $this->buildStorageLocationLookup();

        $itemsPerLocation = $latestBarcodeRows
            ->map(function ($row) use ($locationLookup) {
                $code = $this->extractLocationCodeFromBarcode($row->barcode);
                $label = $code ? ($locationLookup[$code] ?? 'Unknown Location') : 'Unknown Location';

                return [
                    'item_id' => $row->item_id,
                    'label' => $label,
                ];
            })
            ->groupBy('label')
            ->map(fn ($rows, $label) => [
                'label' => $label,
                'total' => collect($rows)->pluck('item_id')->unique()->count(),
            ])
            ->values()
            ->sortByDesc('total')
            ->values();

        $stockBaseQuery = (clone $base)
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->selectRaw(
                'items.id as item_id,' .
                ' SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) as total_ingoing,' .
                ' SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity WHEN transactions.transac_type = "outgoing" THEN -ABS(transactions.quantity) ELSE 0 END) as remaining_quantity'
            )
            ->groupBy('items.id');

        $percentageExpr = 'CASE WHEN total_ingoing <> 0 THEN remaining_quantity / total_ingoing ELSE 0 END';

        $stockBuckets = [
            'empty' => (clone $stockBaseQuery)->havingRaw("$percentageExpr <= 0")->count(),
            'low' => (clone $stockBaseQuery)->havingRaw("$percentageExpr > 0 AND $percentageExpr <= 0.25")->count(),
            'mid' => (clone $stockBaseQuery)->havingRaw("$percentageExpr > 0.25 AND $percentageExpr <= 0.75")->count(),
            'high' => (clone $stockBaseQuery)->havingRaw("$percentageExpr > 0.75")->count(),
        ];

        return [
            'scope' => $scope,
            'range' => [
                'start' => $start?->toDateTimeString(),
                'end' => $end?->toDateTimeString(),
            ],
            'filters' => [
                'date' => $parameters->get('date'),
                'week' => $parameters->get('week'),
                'month' => $parameters->get('month'),
                'year' => $parameters->get('year'),
            ],
            'totals' => [
                'incoming' => (float) $totalIncoming,
                'outgoing' => (float) $totalOutgoing,
            ],
            'recent_transactions' => $recentTransactions,
            'items_per_category' => $itemsPerCategory,
            'items_per_location' => $itemsPerLocation,
            'items_per_project_code' => $itemsPerProjectCode,
            'stock_buckets' => $stockBuckets,
        ];
    }

    private function resolveDashboardDateRange(string $scope, Collection $parameters): array
    {
        $now = Carbon::now();

        return match ($scope) {
            'day' => [$now->copy()->subDay(), $now->copy()],
            'daily' => $this->resolveDailyRange((string) $parameters->get('date')),
            'week' => [$now->copy()->subHours(168), $now->copy()],
            'weekly' => $this->resolveWeeklyRange((string) $parameters->get('week')),
            'month' => [$now->copy()->subMonth(), $now->copy()],
            'monthly' => $this->resolveMonthlyRange((string) $parameters->get('month')),
            'year' => [$now->copy()->subDays(365), $now->copy()],
            'yearly' => $this->resolveYearlyRange((string) $parameters->get('year')),
            default => $this->resolveMonthlyRange((string) $parameters->get('month')),
        };
    }

    private function resolveDailyRange(?string $date): array
    {
        $selected = $this->parseDashboardDate($date) ?? Carbon::now();

        return [$selected->copy()->startOfDay(), $selected->copy()->endOfDay()];
    }

    private function resolveWeeklyRange(?string $week): array
    {
        if (is_string($week) && preg_match('/^(\d{4})-W(\d{2})$/', $week, $matches)) {
            $selected = Carbon::now()->setISODate((int) $matches[1], (int) $matches[2]);

            return [$selected->copy()->startOfWeek(), $selected->copy()->endOfWeek()];
        }

        $selected = Carbon::now();

        return [$selected->copy()->startOfWeek(), $selected->copy()->endOfWeek()];
    }

    private function resolveMonthlyRange(?string $month): array
    {
        if (is_string($month) && preg_match('/^(\d{4})-(\d{2})$/', $month, $matches)) {
            $selected = Carbon::createFromDate((int) $matches[1], (int) $matches[2], 1);

            return [$selected->copy()->startOfMonth(), $selected->copy()->endOfMonth()];
        }

        $selected = Carbon::now();

        return [$selected->copy()->startOfMonth(), $selected->copy()->endOfMonth()];
    }

    private function resolveYearlyRange(?string $year): array
    {
        if (is_string($year) && preg_match('/^\d{4}$/', $year)) {
            $selected = Carbon::createFromDate((int) $year, 1, 1);

            return [$selected->copy()->startOfYear(), $selected->copy()->endOfYear()];
        }

        $selected = Carbon::now();

        return [$selected->copy()->startOfYear(), $selected->copy()->endOfYear()];
    }

    private function parseDashboardDate(?string $date): ?Carbon
    {
        if (!is_string($date) || trim($date) === '') {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildStorageLocationLookup(): array
    {
        return collect($this->optionRepo->getStorageLocations())
            ->mapWithKeys(function (array $location) {
                $code = $this->normalizeLocationCode($location['name'] ?? null);
                $label = $location['label'] ?? null;

                if (!$code || !$label) {
                    return [];
                }

                return [$code => $label];
            })
            ->toArray();
    }

    private function extractLocationCodeFromBarcode(?string $barcode): ?string
    {
        if (!$barcode || !preg_match('/CBC-(\d+)-/i', $barcode, $matches)) {
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
}
