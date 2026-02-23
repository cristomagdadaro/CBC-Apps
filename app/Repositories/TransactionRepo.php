<?php

namespace App\Repositories;

use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Support\Collection;
use Illuminate\Pipeline\Pipeline;
use App\Pipelines\InventoryTransaction\ResolveUserByEmployeeId;
use App\Pipelines\InventoryTransaction\AssignTransactionUuid;
use App\Pipelines\InventoryTransaction\PersistTransaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class TransactionRepo extends AbstractRepoService
{
    public function __construct(Transaction $model)
    {
        parent::__construct($model);
        $this->appendWith = ['item', 'user','personnel'];
    }

    public function getRemainingStocks(Collection $parameters, array $consumableCategoryIds = [1, 2, 3, 5, 6]): Collection
    {
        $search   = $parameters->get('search');
        $isExact  = filter_var($parameters->get('is_exact', false), FILTER_VALIDATE_BOOLEAN);
        $sort     = $parameters->get('sort', 'id');
        $order    = strtolower($parameters->get('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage  = $parameters->get('per_page', '*');
        $page     = (int) $parameters->get('page', 1);
        $paginate = filter_var($parameters->get('paginate', true), FILTER_VALIDATE_BOOLEAN);
        $filter    = $parameters->get('filter');
        $filterBy  = $parameters->get('filter_by');
        $minRemaining = $parameters->get('min_remaining');

        $orderByRaw = match ($sort) {
            'name'               => 'items.name',
            'brand'              => 'items.brand',
            'unit'               => 'transactions.unit',
            'barcode'            => 'transactions.barcode',
            'barcode_prri'       => 'transactions.barcode_prri',
            'total_ingoing'      => 'total_ingoing',
            'total_outgoing'     => 'total_outgoing',
            'remaining_quantity' => 'remaining_quantity',
            'expiration'         => 'transactions.expiration',
            default              => 'items.id',
        };

        $query = $this->model->newQuery()->selectRaw(
                'items.name, items.description, items.brand, transactions.unit, items.id as item_id, transactions.barcode, transactions.barcode_prri,' .
                ' SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) as total_ingoing,' .
                ' SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END) as total_outgoing,' .
                ' (SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) - ' .
                '  SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END)) as remaining_quantity,' .
                ' transactions.expiration,' .
                ' CASE ' .
                '   WHEN transactions.expiration IS NULL THEN 0 ' .
                '   WHEN transactions.expiration < CURDATE() THEN 3 ' .
                '   WHEN transactions.expiration <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 2 ' .
                '   ELSE 1 ' .
                ' END as expiration_priority'
            )->join('items', 'transactions.item_id', '=', 'items.id')
            ->groupBy('items.id', 'items.name', 'items.brand', 'transactions.unit', 'transactions.barcode', 'transactions.barcode_prri', 'transactions.expiration');

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
                $query->whereRaw('0 = 1');
            }
        } elseif (!$filter && !empty($consumableCategoryIds)) {
            // Apply consumable category filter by default
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
            if ($search) {
                $like = '%' . $search . '%';
                $query->havingRaw('barcode LIKE ?', [$like]);
            }
        }

        if ($minRemaining !== null && is_numeric($minRemaining)) {
            $query->havingRaw('remaining_quantity >= ?', [(float) $minRemaining]);
        }

        if ($search !== null && $search !== '') {
            if ($isExact) {
                $query->where(function ($q) use ($search) {
                    $q->where('items.name', $search)
                        ->orWhere('items.brand', $search)
                        ->orWhere('transactions.unit', $search)
                        ->orWhere('transactions.barcode', $search)
                        ->orWhere('transactions.barcode_prri', $search);
                });

                if (is_numeric($search)) {
                    $query->havingRaw(
                        'total_outgoing = ? OR total_ingoing = ? OR remaining_quantity = ?',
                        [$search, $search, $search]
                    );
                }
            } else {
                $like = '%' . $search . '%';

                $query->where(function ($q) use ($like) {
                    $q->where('items.name', 'like', $like)
                        ->orWhere('items.brand', 'like', $like)
                        ->orWhere('transactions.unit', 'like', $like)
                        ->orWhere('transactions.barcode', 'like', $like)
                        ->orWhere('transactions.barcode_prri', 'like', $like);
                });

                if (is_numeric($search)) {
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

    public function getRemainingStocksPerCategory(Collection $parameters, string $categoryName): Collection
    {
        $params = $parameters->merge([
            'filter' => 'category',
            'filter_by' => $categoryName,
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
}
