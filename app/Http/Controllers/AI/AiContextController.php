<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Personnel;
use App\Models\Research\ResearchProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\Inventory\InventoryReportService;
use Illuminate\Http\Request;

/**
 * Provides structured context data for the SproutAi server sync pipeline.
 * These endpoints are only accessible with a valid SPROUTAI_INTERNAL_SYNC_TOKEN.
 */
class AiContextController extends Controller
{
    /**
     * Returns the current inventory levels of all items (supplies + equipment)
     * for SproutAi to answer questions like:
     *   "Do you have ethanol?" / "How many gloves are left?"
     */
    public function inventory(Request $request, InventoryReportService $reportService): JsonResponse
    {
        $parameters = collect([
            'include_all_categories' => false,
            'paginate' => false,
            'per_page' => '*',
        ]);

        $balances = $reportService->getRemainingStocks($parameters)
            ->map(fn($row) => [
                'id'               => $row->item_id ?? $row->id,
                'name'             => $row->name,
                'brand'            => $row->brand,
                'description'      => $row->description,
                'barcode'          => $row->barcode,
                'current_quantity' => (float) $row->remaining_quantity,
                'unit'             => $row->unit ?? 'pcs',
                'availability'     => (float) $row->remaining_quantity > 0 ? 'available' : 'out of stock',
            ]);

        return response()->json(['data' => $balances]);
    }
}
