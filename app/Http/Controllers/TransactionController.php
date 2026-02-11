<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransactionRequest;
use App\Http\Requests\GetTransactionRequest;
use App\Http\Requests\NewOutgoingRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\NewBarcode;
use App\Repositories\TransactionRepo;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class TransactionController extends BaseController
{
    public function __construct(TransactionRepo $repository)
    {
        $this->service = $repository;
    }

    protected function repo(): TransactionRepo
    {
        return $this->service;
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
        return $this->repo()->getRemainingStocks(new Collection($request->all()));
    }

    public function outgoingStockStore(NewOutgoingRequest $request): Model | JsonResponse
    {
        return $this->repo()->createOutgoingWithPipeline($request->validated());
    }

    public function getRemainingStocksPerCategory(GetTransactionRequest $request, string $categoryName): Collection
    {
        return $this->repo()->getRemainingStocksPerCategory(new Collection($request->all()), $categoryName);
    }
}
