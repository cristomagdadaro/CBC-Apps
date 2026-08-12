<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\Inventory\InventoryReportService;
use App\Services\Laboratory\LaboratoryLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AiContextController extends Controller
{
    public function __construct(
        private readonly InventoryReportService $inventoryReportService,
        private readonly LaboratoryLogService $laboratoryLogService
    ) {}

    /**
     * Endpoint for SproutAi to ingest inventory and equipment context.
     *
     * @return JsonResponse
     */
    public function inventory(Request $request): JsonResponse
    {
        $page = (int) $request->get('page', 1);
        $perPage = 25; // Fetch 25 from each to make a 50-item page

        // 1. Fetch Inventory (Consumables/Stock)
        $inventoryParams = collect(['paginate' => true, 'per_page' => $perPage, 'page' => $page]);
        $inventoryPaginator = $this->inventoryReportService->getRemainingStocks($inventoryParams);

        $mappedInventory = collect($inventoryPaginator->get('data'))->map(function ($item) {
            $brand = $item->brand ?? 'No Brand';
            $fullDescription = "Brand: {$brand}\n";
            if (!empty($item->description)) $fullDescription .= "Description: {$item->description}\n";
            if (isset($item->barcode)) $fullDescription .= "Barcode: {$item->barcode}\n";
            if (isset($item->total_ingoing)) $fullDescription .= "Total Ingoing: {$item->total_ingoing} {$item->unit}\n";
            if (isset($item->total_outgoing)) $fullDescription .= "Total Outgoing: {$item->total_outgoing} {$item->unit}\n";
            if (isset($item->remaining_quantity)) $fullDescription .= "Remaining Quantity: {$item->remaining_quantity} {$item->unit}\n";

            return [
                'id' => 'inv_' . ($item->item_id ?? uniqid()),
                'name' => $item->name,
                'short_description' => $brand,
                'description' => $fullDescription,
                'type' => 'inventory',
                'status' => isset($item->remaining_quantity) && $item->remaining_quantity > 0 ? 'In Stock' : 'Out of Stock',
                'url' => null, 
                'created_at' => now(), 
                'updated_at' => now(), 
            ];
        });

        // 2. Fetch Equipment and Usage Logs
        $equipmentPaginator = $this->laboratoryLogService->paginateEquipmentUsage(['per_page' => $perPage, 'page' => $page]);

        $mappedEquipment = collect($equipmentPaginator->items())->map(function ($item) {
            $brand = $item->brand ?? 'No Brand';
            $fullDescription = "Category: {$item->category_name}\nBrand: {$brand}\n";
            if (!empty($item->description)) $fullDescription .= "Description: {$item->description}\n";
            if (isset($item->barcode)) $fullDescription .= "Barcode: {$item->barcode}\n";
            $fullDescription .= "Total Usage Logs: {$item->total_logs}\n";
            $fullDescription .= "Active Users Currently: {$item->active_logs}\n";
            $fullDescription .= "Completed Uses: {$item->completed_logs}\n";

            return [
                'id' => 'eq_' . ($item->id ?? uniqid()),
                'name' => $item->name,
                'short_description' => "{$item->category_name} - {$brand}",
                'description' => $fullDescription,
                'type' => 'equipment',
                'status' => $item->active_logs > 0 ? 'In Use' : 'Available',
                'url' => null, 
                'created_at' => now(), 
                'updated_at' => $item->last_logged_at ?? now(), 
            ];
        });

        // Combine the results
        $combinedData = $mappedInventory->merge($mappedEquipment);

        // Determine if there are more pages based on either service having more data
        $hasMorePages = false;
        if (isset($inventoryPaginator['last_page']) && $page < $inventoryPaginator['last_page']) $hasMorePages = true;
        if ($equipmentPaginator->hasMorePages()) $hasMorePages = true;

        return response()->json([
            'current_page' => $page,
            'data' => $combinedData,
            'last_page' => $hasMorePages ? $page + 1 : $page,
        ]);
    }
}
