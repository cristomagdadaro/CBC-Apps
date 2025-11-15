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
        $search = $request->input('search');
        $is_exact = $request->input('is_exact', false);
        $sort = $request->input('sort', 'id');
        $order = $request->input('order', 'asc');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $paginate = $request->input('paginate', true);

        $orderByRaw = match ($sort) {
            'name' => 'items.name',
            'brand' => 'items.brand',
            'unit' => 'transactions.unit',
            'total_ingoing' => 'SUM(CASE WHEN transactions.quantity > 0 THEN transactions.quantity ELSE 0 END)',
            'total_outgoing' => 'SUM(CASE WHEN transactions.quantity < 0 THEN ABS(transactions.quantity) ELSE 0 END)',
            'remaining_quantity' => 'SUM(transactions.quantity)',
            default => 'items.id',
        };

        $data = $this->service->model
            ->selectRaw('items.name, items.brand, transactions.unit, items.id as item_id,
                     SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity ELSE 0 END) as total_ingoing,
                     SUM(CASE WHEN transactions.transac_type = "outgoing" THEN transactions.quantity ELSE 0 END) as total_outgoing,
                     SUM(CASE WHEN transactions.transac_type = "incoming" THEN transactions.quantity WHEN transactions.transac_type = "outgoing" THEN -transactions.quantity ELSE 0 END) as remaining_quantity')
            ->join('items', 'transactions.item_id', '=', 'items.id')
            ->where(function ($query) use ($search, $is_exact) {
                if ($is_exact && $search) {
                    $query->where('items.name', $search)
                        ->orWhere('items.brand', $search)
                        ->orWhere('transactions.unit', $search)
                        ->having('total_outgoing', $search)
                        ->having('total_ingoing', $search)
                        ->having('remaining_quantity', $search);
                } else {
                    $query->where('items.name', 'like', '%' . $search . '%')
                        ->orWhere('items.brand', 'like', '%' . $search . '%')
                        ->orWhere('transactions.unit', 'like', '%' . $search . '%')
                        ->having('total_outgoing', 'like', '%' . $search . '%')
                        ->having('total_ingoing', 'like', '%' . $search . '%')
                        ->having('remaining_quantity', 'like', '%' . $search . '%');
                }
            })
            ->groupBy('items.id', 'items.name', 'items.brand', 'transactions.unit')
            ->orderByRaw($orderByRaw . ' ' . $order);
        $count = $data->count();
        if ($paginate) {
            $data = $data->paginate($perPage, ['*'], 'page', $page);
        } else {
            $data = $data->get();
        }

        return new Collection($data);
    }

    public function outgoingStockStore(NewOutgoingRequest $request): Model | JsonResponse
    {
        $validated = $request->validated();

        // If no user_id was provided but employee_id was, resolve the user and set user_id
        if (empty($validated['user_id']) && !empty($validated['employee_id'])) {
            $user = User::where('employee_id', $validated['employee_id'])->first();
            if ($user) {
                $validated['user_id'] = $user->id;
            }
        }

        $validated['quantity'] = $validated['quantity'] * -1;
        $validated['id'] = (string) Str::uuid();
        return $this->service->create($validated);
    }
}
