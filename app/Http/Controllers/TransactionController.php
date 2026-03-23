<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTransactionRequest;
use App\Http\Requests\GetTransactionRequest;
use App\Http\Requests\InventoryRecountAdjustmentRequest;
use App\Http\Requests\NewOutgoingRequest;
use App\Http\Requests\UpdateTransactionRequest;
use App\Models\NewBarcode;
use App\Repositories\TransactionRepo;
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

    public function destroy(Request $request, string $id): Model | JsonResponse
    {
        if ($request->boolean('force')) {
            $deleted = $this->repo()->forceDelete($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction permanently deleted.',
                'data' => [
                    'id' => $deleted->id,
                    'barcode' => $deleted->barcode,
                    'force' => true,
                ],
            ]);
        }

        return parent::_destroy($id);
    }

    public function remainingStocks(Request $request): Collection
    {
        return $this->repo()->getRemainingStocks(new Collection($request->all()), [1,2,3,5,6,11,12]);
    }

    public function projectCodes(): JsonResponse
    {
        return response()->json([
            'data' => $this->repo()->getAvailableProjectCodes(),
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->repo()->getInventoryDashboardMetrics(new Collection($request->all())),
        ]);
    }

    public function outgoingStockStore(NewOutgoingRequest $request): Model | JsonResponse
    {
        return $this->repo()->createOutgoingWithPipeline($request->validated());
    }

    public function recountLookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode' => ['required', 'string', 'max:191'],
        ]);

        $result = $this->repo()->findRecountingCandidateByBarcode($validated['barcode']);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'No inventory item found for the scanned barcode.',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }

    public function recountAdjust(InventoryRecountAdjustmentRequest $request): JsonResponse
    {
        $result = $this->repo()->applyInventoryRecountAdjustment(
            payload: $request->validated(),
            userId: optional($request->user())->id,
        );

        return response()->json([
            'status' => 'success',
            'message' => $result['transaction_created']
                ? 'Inventory adjustment recorded successfully.'
                : 'No discrepancy found. Inventory count is already aligned.',
            'data' => $result,
        ]);
    }

    public function getRemainingStocksPerCategory(GetTransactionRequest $request, string $categoryName): Collection
    {
        return $this->repo()->getRemainingStocksPerCategory(new Collection($request->all()), $categoryName);
    }
}
