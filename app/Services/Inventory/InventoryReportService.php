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
        $query = $this->buildRemainingStocksBaseQuery();

        $this->applyRemainingStocksCategoryFilter($query, $parameters, $consumableCategoryIds);
        $this->applyRemainingStocksStockLevelFilter($query, $parameters);
        $this->applyRemainingStocksProjectCodeFilter($query, $parameters);
        $this->applyRemainingStocksBarcodeFilter($query, $parameters);
        $this->applyRemainingStocksLocationFilter($query, $parameters);
        $this->applyRemainingStocksMinRemainingFilter($query, $parameters);
        $this->applyRemainingStocksSearchFilter($query, $parameters);
        $this->applyRemainingStocksSorting($query, $parameters);

        return $this->executeRemainingStocksQuery($query, $parameters);
    }

    private function buildRemainingStocksBaseQuery(): Builder
    {
        return $this->transactionModel->newQuery()->selectRaw(
            'items.name, items.description, items.brand, items.id as item_id, transactions.barcode,' .
                ' ' . $this->canonicalTransactionFieldExpression('unit') . ' as unit,' .
                ' ' . $this->canonicalTransactionFieldExpression('barcode_prri') . ' as barcode_prri,' .
                ' ' . $this->canonicalTransactionFieldExpression('project_code') . ' as project_code,' .
                $this->canonicalStockCalculationExpression() . ',' .
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
    }

    private function applyRemainingStocksCategoryFilter(Builder $query, Collection $parameters, array $consumableCategoryIds): void
    {
        $filter = $parameters->get('filter');
        $filterBy = $parameters->get('filter_by');
        $categoryFilter = $parameters->get('category_filter');
        $includeAllCategories = filter_var($parameters->get('include_all_categories', false), FILTER_VALIDATE_BOOLEAN);

        $activeCategoryFilter = $categoryFilter ?? ($filter === 'category' ? $filterBy : null);

        if ($activeCategoryFilter) {
            $values = is_array($activeCategoryFilter) ? $activeCategoryFilter : [$activeCategoryFilter];
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
                })->pluck('id')->toArray();
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
        } elseif (!$includeAllCategories && !empty($consumableCategoryIds)) {
            $query->whereIn('items.category_id', $consumableCategoryIds);
        }
    }

    private function applyRemainingStocksStockLevelFilter(Builder $query, Collection $parameters): void
    {
        $filter = $parameters->get('filter');
        $filterBy = $parameters->get('filter_by');
        $stockLevelFilter = $parameters->get('stock_level_filter');

        $activeStockLevelFilter = $stockLevelFilter ?? ($filter === 'quantity' ? $filterBy : null);

        if ($activeStockLevelFilter) {
            $percentageExpr = 'CASE WHEN total_ingoing <> 0 THEN remaining_quantity / total_ingoing ELSE 0 END';

            switch ($activeStockLevelFilter) {
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
        }
    }

    private function applyRemainingStocksProjectCodeFilter(Builder $query, Collection $parameters): void
    {
        $filter = $parameters->get('filter');
        $filterBy = $parameters->get('filter_by');
        $projectCodeFilter = $parameters->get('project_code_filter');

        $activeProjectCodeFilter = $projectCodeFilter ?? ($filter === 'project_code' ? $filterBy : null);

        if ($activeProjectCodeFilter) {
            $query->where('transactions.project_code', $activeProjectCodeFilter);
        }
    }

    private function applyRemainingStocksBarcodeFilter(Builder $query, Collection $parameters): void
    {
        $filter = $parameters->get('filter');
        $rawSearch = $parameters->get('search');
        $searchTerm = $rawSearch !== null ? trim((string) $rawSearch) : '';
        $hasSearchTerm = $searchTerm !== '';

        if ($filter === 'barcode' && $hasSearchTerm) {
            $like = '%' . $searchTerm . '%';
            $query->havingRaw('barcode LIKE ?', [$like]);
        }
    }

    private function applyRemainingStocksLocationFilter(Builder $query, Collection $parameters): void
    {
        $storageLocationId = $parameters->get('storage_location_id');
        $storageLocationCode = $this->normalizeLocationCode($storageLocationId);

        if ($storageLocationCode !== null) {
            $query->where('transactions.barcode', 'like', "CBC-{$storageLocationCode}-%");
        }
    }

    private function applyRemainingStocksMinRemainingFilter(Builder $query, Collection $parameters): void
    {
        $minRemaining = $parameters->get('min_remaining');

        if ($minRemaining !== null && is_numeric($minRemaining)) {
            $query->havingRaw('remaining_quantity >= ?', [(float) $minRemaining]);
        }
    }

    private function applyRemainingStocksSearchFilter(Builder $query, Collection $parameters): void
    {
        $rawSearch = $parameters->get('search');
        $searchTerm = $rawSearch !== null ? trim((string) $rawSearch) : '';
        $hasSearchTerm = $searchTerm !== '';
        $isExact = filter_var($parameters->get('is_exact', false), FILTER_VALIDATE_BOOLEAN);

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
    }

    private function applyRemainingStocksSorting(Builder $query, Collection $parameters): void
    {
        $sort = $parameters->get('sort', 'id');
        $order = strtolower($parameters->get('order', 'asc')) === 'desc' ? 'desc' : 'asc';

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

        if ($sort === 'name') {
            $query->orderByRaw('expiration_priority ASC')
                ->orderByRaw('items.name ' . $order);
        } else {
            $query->orderByRaw($orderByRaw . ' ' . $order);
        }
    }

    private function executeRemainingStocksQuery(Builder $query, Collection $parameters): Collection
    {
        $perPage = $parameters->get('per_page', '*');
        $page = (int) $parameters->get('page', 1);
        $paginate = filter_var($parameters->get('paginate', true), FILTER_VALIDATE_BOOLEAN);

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

        $base = $this->transactionModel->newQuery()->when($start && $end, function (Builder $query) use ($start, $end) {
            $query->whereBetween('transactions.created_at', [$start, $end]);
        });

        $topPersonnel = $this->getDashboardTopPersonnel($base);

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
            'totals' => $this->getDashboardTotals($base),
            'top_issued_items' => $this->getDashboardTopIssuedItems($base),
            'top_personnel_by_volume' => $topPersonnel['by_volume'],
            'top_personnel_by_transaction' => $topPersonnel['by_transaction'],
            'recent_transactions' => $this->getDashboardRecentTransactions($base),
            'items_per_category' => $this->getDashboardItemsPerCategory($base),
            'items_per_location' => $this->getDashboardItemsPerLocation($base),
            'items_per_project_code' => $this->getDashboardItemsPerProjectCode($base),
            'stock_buckets' => $this->getDashboardStockBuckets($base),
        ];
    }

    private function getDashboardTotals(Builder $base): array
    {
        $incomingCount = (clone $base)->where('transactions.transac_type', 'incoming')->count();
        $outgoingCount = (clone $base)->where('transactions.transac_type', 'outgoing')->count();
        $totalIncomingQuantity = (float) ((clone $base)->where('transactions.transac_type', 'incoming')->sum('transactions.quantity') ?: 0);
        $totalOutgoingQuantity = (float) ((clone $base)->where('transactions.transac_type', 'outgoing')->sum(DB::raw('ABS(transactions.quantity)')) ?: 0);

        return [
            'incoming' => (int) $incomingCount,
            'outgoing' => (int) $outgoingCount,
            'incoming_count' => (int) $incomingCount,
            'outgoing_count' => (int) $outgoingCount,
            'incoming_quantity' => $totalIncomingQuantity,
            'outgoing_quantity' => $totalOutgoingQuantity,
            'total_transactions' => (int) ($incomingCount + $outgoingCount),
        ];
    }

    private function getDashboardTopIssuedItems(Builder $base, int $limit = 5): Collection
    {
        return (clone $base)
            ->where('transactions.transac_type', 'outgoing')
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->selectRaw('items.name, items.brand, items.description, SUM(ABS(transactions.quantity)) as total_quantity, COUNT(transactions.id) as transac_count')
            ->groupBy('items.id', 'items.name', 'items.brand', 'items.description')
            ->orderByDesc('total_quantity')
            ->limit($limit)
            ->get();
    }

    private function getDashboardRecentTransactions(Builder $base, int $limit = 10): Collection
    {
        return (clone $base)
            ->with(['item', 'personnel', 'user'])
            ->orderByDesc('transactions.created_at')
            ->limit($limit)
            ->get();
    }

    private function getDashboardItemsPerCategory(Builder $base): Collection
    {
        return (clone $base)
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as label, COUNT(DISTINCT transactions.item_id) as total')
            ->groupBy('categories.name')
            ->orderByDesc('total')
            ->get();
    }

    private function getDashboardItemsPerProjectCode(Builder $base): Collection
    {
        return (clone $base)
            ->whereNotNull('transactions.project_code')
            ->where('transactions.project_code', '!=', '')
            ->selectRaw('transactions.project_code as label, COUNT(DISTINCT transactions.item_id) as total')
            ->groupBy('transactions.project_code')
            ->orderByDesc('total')
            ->get();
    }

    private function getDashboardItemsPerLocation(Builder $base): Collection
    {
        $latestBarcodeRows = (clone $base)
            ->whereNotNull('transactions.item_id')
            ->whereNotNull('transactions.barcode')
            ->select(['transactions.item_id', 'transactions.barcode'])
            ->orderByDesc('transactions.created_at')
            ->get()
            ->unique('item_id');

        $locationLookup = $this->buildStorageLocationLookup();

        return $latestBarcodeRows
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
    }

    private function getDashboardStockBuckets(Builder $base): array
    {
        $stockBaseQuery = (clone $base)
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->selectRaw(
                'items.id as item_id,' .
                    $this->canonicalStockCalculationExpression()
            )
            ->groupBy('items.id');

        $percentageExpr = 'CASE WHEN total_ingoing <> 0 THEN remaining_quantity / total_ingoing ELSE 0 END';

        return [
            'empty' => (clone $stockBaseQuery)->havingRaw("$percentageExpr <= 0")->count(),
            'low' => (clone $stockBaseQuery)->havingRaw("$percentageExpr > 0 AND $percentageExpr <= 0.25")->count(),
            'mid' => (clone $stockBaseQuery)->havingRaw("$percentageExpr > 0.25 AND $percentageExpr <= 0.75")->count(),
            'high' => (clone $stockBaseQuery)->havingRaw("$percentageExpr > 0.75")->count(),
        ];
    }

    private function getDashboardTopPersonnel(Builder $base, int $limit = 5): array
    {
        $personnelStats = (clone $base)
            ->where('transactions.transac_type', 'outgoing')
            ->join('personnels', 'transactions.personnel_id', '=', 'personnels.id')
            ->selectRaw("
                CONCAT(personnels.fname, ' ', personnels.lname) as name, 
                personnels.position, 
                personnels.employee_id, 
                SUM(ABS(transactions.quantity)) as total_volume, 
                COUNT(transactions.id) as transac_count
            ")
            ->groupBy(
                'personnels.id', 
                'personnels.fname', 
                'personnels.lname', 
                'personnels.position', 
                'personnels.employee_id'
            );

        return [
            'by_volume' => (clone $personnelStats)->orderByDesc('total_volume')->limit($limit)->get(),
            'by_transaction' => (clone $personnelStats)->orderByDesc('transac_count')->limit($limit)->get(),
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

    public function canonicalTransactionFieldExpression(string $field): string
    {
        $qualifiedField = "transactions.{$field}";

        return 'COALESCE(' .
            'NULLIF(MAX(CASE WHEN transactions.transac_type = "incoming" AND ' . $qualifiedField . ' IS NOT NULL AND TRIM(' . $qualifiedField . ') <> "" THEN ' . $qualifiedField . ' END), ""), ' .
            'NULLIF(MAX(CASE WHEN ' . $qualifiedField . ' IS NOT NULL AND TRIM(' . $qualifiedField . ') <> "" THEN ' . $qualifiedField . ' END), "")' .
            ')';
    }

    public function canonicalStockCalculationExpression(
        string $incomingAlias = 'total_ingoing', 
        string $outgoingAlias = 'total_outgoing', 
        string $remainingAlias = 'remaining_quantity'
    ): string {
        return ' SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) as ' . $incomingAlias . ',' .
               ' SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END) as ' . $outgoingAlias . ',' .
               ' (SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) - ' .
               '  SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END)) as ' . $remainingAlias;
    }
}
