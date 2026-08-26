<?php

namespace App\Services\Inventory;

use App\Enums\Inventory;
use App\Models\LaboratoryEquipmentLocationSurvey;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryRecountService
{
    public function __construct(
        private readonly Transaction $transactionModel,
        private readonly InventoryReportService $reportService
    ) {}

    public function findRecountingCandidateByBarcode(string $barcode): ?array
        {
            $needle = trim($barcode);

            if ($needle === '') {
                return null;
            }

            $summary = $this->transactionModel->newQuery()
                ->selectRaw(
                    'items.id as item_id,
                    items.name,
                    items.brand,
                    items.description,
                    transactions.barcode,
                    ' . $this->reportService->canonicalTransactionFieldExpression('barcode_prri') . ' as barcode_prri,
                    ' . $this->reportService->canonicalTransactionFieldExpression('unit') . ' as unit,
                    ' . $this->reportService->canonicalTransactionFieldExpression('project_code') . ' as project_code,
                    ' . $this->reportService->canonicalStockCalculationExpression('total_incoming', 'total_outgoing', 'remaining_quantity') . ',
                    MAX(transactions.created_at) as latest_transaction_at'
                )
                ->join('items', 'transactions.item_id', '=', 'items.id')
                ->where(function (Builder $query) use ($needle) {
                    $query->where('transactions.barcode', $needle)
                        ->orWhere('transactions.barcode_prri', $needle);
                })
                ->groupBy(
                    'items.id',
                    'items.name',
                    'items.brand',
                    'items.description',
                    'transactions.barcode'
                )
                ->orderByDesc('latest_transaction_at')
                ->first();

            if (!$summary) {
                return null;
            }

            $locationSurvey = LaboratoryEquipmentLocationSurvey::query()
                ->where('equipment_id', $summary->item_id)
                ->first();

            return [
                'item_id' => (string) $summary->item_id,
                'name' => (string) ($summary->name ?? ''),
                'brand' => (string) ($summary->brand ?? ''),
                'description' => (string) ($summary->description ?? ''),
                'barcode' => (string) ($summary->barcode ?? ''),
                'barcode_prri' => (string) ($summary->barcode_prri ?? ''),
                'unit' => (string) ($summary->unit ?? ''),
                'project_code' => (string) ($summary->project_code ?? ''),
                'total_incoming' => (int) ($summary->total_incoming ?? 0),
                'total_outgoing' => (int) ($summary->total_outgoing ?? 0),
                'remaining_quantity' => (int) ($summary->remaining_quantity ?? 0),
                'latest_transaction_at' => $summary->latest_transaction_at,
                'location' => $locationSurvey
                    ? [
                        'location_code' => $locationSurvey->location_code,
                        'location_label' => $locationSurvey->location_label,
                        'reported_at' => $locationSurvey->reported_at,
                    ]
                    : null,
            ];
        }

    public function applyInventoryRecountAdjustment(array $payload, ?string $userId = null): array
        {
            $candidate = $this->findRecountingCandidateByBarcode((string) ($payload['barcode'] ?? ''));

            if (!$candidate) {
                throw ValidationException::withMessages([
                    'barcode' => 'No inventory item matched the scanned barcode.',
                ]);
            }

            $systemCount = (int) ($candidate['remaining_quantity'] ?? 0);
            $physicalCount = (int) ($payload['physical_count'] ?? 0);
            $adjustment = $physicalCount - $systemCount;

            return DB::transaction(function () use ($payload, $candidate, $systemCount, $physicalCount, $adjustment, $userId) {
                $locationUpdated = false;

                $locationCode = isset($payload['location_code'])
                    ? trim((string) $payload['location_code'])
                    : null;
                $locationLabel = isset($payload['location_label'])
                    ? trim((string) $payload['location_label'])
                    : null;

                if (($locationCode && $locationLabel) || (!$locationCode && $locationLabel)) {
                    LaboratoryEquipmentLocationSurvey::query()->updateOrCreate(
                        ['equipment_id' => $candidate['item_id']],
                        [
                            'personnel_id' => null,
                            'location_code' => $locationCode ?: null,
                            'location_label' => $locationLabel,
                            'reported_at' => Carbon::now(),
                        ]
                    );

                    $locationUpdated = true;
                }

                $transaction = null;

                if ($adjustment !== 0) {
                    $transacType = $adjustment > 0
                        ? Inventory::INCOMING->value
                        : Inventory::OUTGOING->value;

                    $quantity = abs($adjustment);
                    $resolvedUserId = $userId ?: Auth::id();

                    $remarks = sprintf(
                        'Inventory adjustment via recounting. System count: %d, Physical count: %d, Adjustment: %s%d.',
                        $systemCount,
                        $physicalCount,
                        $adjustment > 0 ? '+' : '-',
                        $quantity,
                    );

                    if ($locationUpdated && !empty($locationLabel)) {
                        $remarks .= sprintf(' Location updated to: %s%s.', $locationCode ? "{$locationCode} - " : '', $locationLabel);
                    }

                    $transaction = $this->transactionModel->newQuery()->create([
                        'item_id' => $candidate['item_id'],
                        'barcode' => $candidate['barcode'] ?: ($payload['barcode'] ?? null),
                        'barcode_prri' => $candidate['barcode_prri'] ?: null,
                        'transac_type' => $transacType,
                        'quantity' => $quantity,
                        'unit' => $candidate['unit'] ?: null,
                        'project_code' => $candidate['project_code'] ?: null,
                        'user_id' => $resolvedUserId,
                        'remarks' => $remarks,
                    ]);
                }

                return [
                    'item' => $this->findRecountingCandidateByBarcode((string) ($payload['barcode'] ?? '')),
                    'system_count' => $systemCount,
                    'physical_count' => $physicalCount,
                    'adjustment' => $adjustment,
                    'transaction_created' => $adjustment !== 0,
                    'location_updated' => $locationUpdated,
                    'transaction' => $transaction,
                ];
            });
        }

}
