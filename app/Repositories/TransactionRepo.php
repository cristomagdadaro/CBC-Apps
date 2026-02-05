<?php

namespace App\Repositories;

use App\Models\Transaction;
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

    public function getRemainingStocks(Collection $parameters): Collection
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
            'total_ingoing'      => 'total_ingoing',
            'total_outgoing'     => 'total_outgoing',
            'remaining_quantity' => 'remaining_quantity',
            default              => 'items.id',
        };

        $query = $this->model
            ->newQuery()
            ->selectRaw(
                'items.name, items.description, items.brand, transactions.unit, items.id as item_id, transactions.barcode,' .
                ' SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) as total_ingoing,' .
                ' SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END) as total_outgoing,' .
                ' (SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) - ' .
                '  SUM(CASE WHEN transactions.transac_type = "outgoing" THEN ABS(transactions.quantity) ELSE 0 END)) as remaining_quantity'
            )
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->groupBy('items.id', 'items.name', 'items.brand', 'transactions.unit', 'transactions.barcode');

        if ($filter === 'category' && $filterBy) {
            $query->where('items.category_id', $filterBy);
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
                        ->orWhere('transactions.barcode', $search);
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
                        ->orWhere('transactions.barcode', 'like', $like);
                });

                if (is_numeric($search)) {
                    $query->orHavingRaw(
                        'CAST(total_outgoing AS CHAR) LIKE ? OR CAST(total_ingoing AS CHAR) LIKE ? OR CAST(remaining_quantity AS CHAR) LIKE ?',
                        [$like, $like, $like]
                    );
                }
            }
        }

        $query->orderByRaw($orderByRaw . ' ' . $order);

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
}
