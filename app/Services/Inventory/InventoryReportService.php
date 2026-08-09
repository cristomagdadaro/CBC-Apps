<?php

namespace App\Services\Inventory;

use App\Models\Category;
use App\Models\Transaction;
use App\Repositories\OptionRepo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryReportService
{
    public function __construct(
        private readonly Transaction $transactionModel,
        private readonly OptionRepo $optionRepo
    ) {}

    public function getRemainingStocks(Collection $parameters, array $consumableCategoryIds = [1, 2, 3, 5, 6, 11, 12]): Collection
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
            'unit'               => 'unit',
            'barcode'            => 'transactions.barcode',
            'barcode_prri'       => 'barcode_prri',
            'total_ingoing'      => 'total_ingoing',
            'total_outgoing'     => 'total_outgoing',
            'remaining_quantity' => 'remaining_quantity',
            'expiration'         => 'expiration',
            default              => 'items.id',
        };

        $query = $this->transactionModel->newQuery()->selectRaw(
            'items.name, items.description, items.brand, items.id as item_id, transactions.barcode,' .
                ' ' . $this->canonicalTransactionFieldExpression('unit') . ' as unit,' .
                ' ' . $this->canonicalTransactionFieldExpression('barcode_prri') . ' as barcode_prri,' .
                ' ' . $this->canonicalTransactionFieldExpression('project_code') . ' as project_code,' .
                ' SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) as total_ingoing,' .
                ' SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END) as total_outgoing,' .
                ' (SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) - ' .
                '  SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END)) as remaining_quantity,' .
                ' MIN(CASE WHEN transactions.transac_type = "incoming" THEN transactions.expiration END) as expiration,' .
                ' CASE ' .
                '   WHEN MIN(CASE WHEN transactions.transac_type = "incoming" THEN transactions.expiration END) IS NULL THEN 2 ' .
                '   WHEN MIN(CASE WHEN transactions.transac_type = "incoming" THEN transactions.expiration END) < CURDATE() THEN 4 ' .
                '   WHEN MIN(CASE WHEN transactions.transac_type = "incoming" THEN transactions.expiration END) <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 3 ' .
                '   ELSE 1 ' .
                ' END as expiration_priority'
        )->join('items', 'transactions.item_id', '=', 'items.id')
            ->whereNotNull('transactions.barcode')
            ->whereRaw('TRIM(transactions.barcode) <> ""')
            ->groupBy('items.id', 'items.name', 'items.description', 'items.brand', 'transactions.barcode');

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

                $stockInfo = $row->barcode !== null
                    ? " - {$row->barcode}"
                    : '';

                return [
                    'value' => $row->barcode ?? $row->item_id,
                    'item_id' => $row->item_id,
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
        return $this->transactionModel
            ->newQuery()
            ->with(['item', 'personnel', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getAvailableProjectCodes(): Collection
    {
        return $this->transactionModel
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
        $scope = strtolower((string) $parameters->get('scope', 'all'));
        [$start, $end] = $this->resolveDashboardDateRange($scope, $parameters);

        $base = $this->transactionModel->newQuery()
            ->when($start && $end, function (Builder $query) use ($start, $end) {
                $query->whereBetween('transactions.created_at', [$start, $end]);
            });

        $incomingCount = (clone $base)
            ->where('transactions.transac_type', 'incoming')
            ->count();

        $outgoingCount = (clone $base)
            ->where('transactions.transac_type', 'outgoing')
            ->count();

        $totalIncomingQuantity = (float) ((clone $base)
            ->where('transactions.transac_type', 'incoming')
            ->sum('transactions.quantity') ?: 0);

        $totalOutgoingQuantity = (float) ((clone $base)
            ->where('transactions.transac_type', 'outgoing')
            ->sum(DB::raw('ABS(transactions.quantity)')) ?: 0);

        $topIssuedItems = (clone $base)
            ->where('transactions.transac_type', 'outgoing')
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->selectRaw('items.name, items.brand, items.description, SUM(ABS(transactions.quantity)) as total_quantity, COUNT(transactions.id) as transac_count')
            ->groupBy('items.id', 'items.name', 'items.brand', 'items.description')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->get();

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
            ->map(fn($rows, $label) => [
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
                'incoming' => (int) $incomingCount,
                'outgoing' => (int) $outgoingCount,
                'incoming_count' => (int) $incomingCount,
                'outgoing_count' => (int) $outgoingCount,
                'incoming_quantity' => (float) $totalIncomingQuantity,
                'outgoing_quantity' => (float) $totalOutgoingQuantity,
                'total_transactions' => (int) ($incomingCount + $outgoingCount),
            ],
            'top_issued_items' => $topIssuedItems,
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
            'all', 'all_time' => [null, null],
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

    private function canonicalTransactionFieldExpression(string $field): string
    {
        $qualifiedField = "transactions.{$field}";

        return 'COALESCE(' .
            'NULLIF(MAX(CASE WHEN transactions.transac_type = "incoming" AND ' . $qualifiedField . ' IS NOT NULL AND TRIM(' . $qualifiedField . ') <> "" THEN ' . $qualifiedField . ' END), ""), ' .
            'NULLIF(MAX(CASE WHEN ' . $qualifiedField . ' IS NOT NULL AND TRIM(' . $qualifiedField . ') <> "" THEN ' . $qualifiedField . ' END), "")' .
            ')';
    }
}
