<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransactionRequest;
use App\Http\Requests\GetTransactionRequest;
use App\Http\Requests\NewOutgoingRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\NewBarcode;
use App\Models\User;
use App\Repositories\TransactionRepo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TransactionController extends BaseController
{
    public function __construct(TransactionRepo $repository)
    {
        $this->service = $repository;
    }

    public function index(GetTransactionRequest $request)
    {
        return parent::_index($request);
    }

    public function create(CreateTransactionRequest $request): Model | JsonResponse
    {
        return parent::_store($request);
    }

    /**
     * Generate a unique barcode 128 ID.
     * @param string $room
     * @return string
     */
    public function generateUniqueBarcode128ID(Request $request, string $room = null): string
    {
        $room = $room ?? $request->get('room');

        return json_encode([
            'data' => [
                'barcode' => NewBarcode::GenerateBarcode($room),
            ],
        ]);

    }

    public function update(UpdateTransactionRequest $request, string $id): Model
    {
        return parent::_update($id, $request);
    }

    public function destroy(string $id): Model
    {
        return parent::_destroy($id);
    }

    public function remainingStocks(Request $request): Collection
    {
        $search   = $request->input('search');
        $isExact  = filter_var($request->input('is_exact', false), FILTER_VALIDATE_BOOLEAN);
        $sort     = $request->input('sort', 'id');
        $order    = strtolower($request->input('order', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage  = $request->input('per_page', '*');
        $page     = (int) $request->input('page', 1);
        $paginate = filter_var($request->input('paginate', true), FILTER_VALIDATE_BOOLEAN);
        $filter    = $request->input('filter');
        $filterBy  = $request->input('filter_by');

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

        $query = $this->service->model
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

    public function outgoingStockStore(NewOutgoingRequest $request): Model | JsonResponse
    {
        $validated = $request->validated();

        if (empty($validated['user_id']) && !empty($validated['employee_id'])) {
            $user = User::where('employee_id', $validated['employee_id'])->first();
            if ($user) {
                $validated['user_id'] = $user->id;
                $validated['personnel_id'] = $user->id;
            }
        }

        $validated['id'] = (string) Str::uuid();
        return $this->service->create($validated);
    }
}
