<?php

namespace App\Console\Commands;

use App\Enums\Inventory;
use App\Models\NewBarcode;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncBarcodeCounters extends Command
{
    protected $signature = 'inventory:sync-barcode-counters {--apply : Apply changes to new_barcodes table}';

    protected $description = 'Safely sync barcode counters from existing transactions without changing printed barcodes';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $roomMaxSequence = [];
        $invalidPatternCount = 0;

        Transaction::query()
            ->whereNotNull('barcode')
            ->where('barcode', 'like', 'CBC-%')
            ->orderBy('id')
            ->chunk(1000, function ($transactions) use (&$roomMaxSequence, &$invalidPatternCount) {
                foreach ($transactions as $transaction) {
                    $barcode = trim((string) $transaction->barcode);

                    if (!preg_match('/^CBC-(\d{2})-(\d{6})$/', $barcode, $matches)) {
                        $invalidPatternCount++;
                        continue;
                    }

                    $room = $matches[1];
                    $sequence = (int) $matches[2];

                    if (!isset($roomMaxSequence[$room]) || $sequence > $roomMaxSequence[$room]) {
                        $roomMaxSequence[$room] = $sequence;
                    }
                }
            });

        if (empty($roomMaxSequence)) {
            $this->warn('No CBC barcodes found in transactions. Nothing to sync.');
            return self::SUCCESS;
        }

        ksort($roomMaxSequence);

        $actions = [];

        foreach ($roomMaxSequence as $room => $maxSequence) {
            $targetBarcode = sprintf('CBC-%s-%06d', $room, $maxSequence);
            $existing = NewBarcode::query()->where('room', $room)->first();

            if (!$existing) {
                $actions[] = [
                    'room' => $room,
                    'action' => 'create',
                    'from' => null,
                    'to' => $targetBarcode,
                ];
                continue;
            }

            $currentBarcode = trim((string) $existing->barcode);
            $currentSequence = $this->extractSequence($currentBarcode, $room);

            if ($currentSequence === null) {
                $actions[] = [
                    'room' => $room,
                    'action' => 'update-invalid',
                    'from' => $currentBarcode,
                    'to' => $targetBarcode,
                ];
                continue;
            }

            if ($currentSequence < $maxSequence) {
                $actions[] = [
                    'room' => $room,
                    'action' => 'update',
                    'from' => $currentBarcode,
                    'to' => $targetBarcode,
                ];
            }
        }

        $incomingDuplicates = Transaction::query()
            ->select('barcode', DB::raw('COUNT(*) as aggregate'))
            ->where('transac_type', Inventory::INCOMING->value)
            ->whereNotNull('barcode')
            ->groupBy('barcode')
            ->having('aggregate', '>', 1)
            ->count();

        $this->info('Barcode sync audit summary:');
        $this->line('- Rooms found in transactions: ' . count($roomMaxSequence));
        $this->line('- Invalid CBC pattern rows: ' . $invalidPatternCount);
        $this->line('- new_barcodes rows to change: ' . count($actions));
        $this->line('- Duplicate INCOMING barcode values: ' . $incomingDuplicates);

        if (!empty($actions)) {
            $previewRows = array_slice($actions, 0, 20);
            $this->table(['room', 'action', 'from', 'to'], $previewRows);

            if (count($actions) > 20) {
                $this->line('... and ' . (count($actions) - 20) . ' more row(s).');
            }
        }

        if (!$apply) {
            $this->warn('Dry run only. No data was changed. Re-run with --apply to persist updates.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($actions) {
            foreach ($actions as $action) {
                if ($action['action'] === 'create') {
                    NewBarcode::query()->create([
                        'room' => $action['room'],
                        'barcode' => $action['to'],
                    ]);

                    continue;
                }

                NewBarcode::query()
                    ->where('room', $action['room'])
                    ->update(['barcode' => $action['to']]);
            }
        });

        $this->info('Barcode counter sync applied successfully. Existing transaction barcodes were not modified.');

        return self::SUCCESS;
    }

    private function extractSequence(string $barcode, string $room): ?int
    {
        $pattern = '/^CBC-' . preg_quote($room, '/') . '-(\d{6})$/';

        if (!preg_match($pattern, $barcode, $matches)) {
            return null;
        }

        return (int) $matches[1];
    }
}
