<?php

use App\Enums\Inventory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const OPTION_KEY = 'equipment_logger_modes';
    private const DEFAULT_MODE = 'tracked_only';
    private const EXCLUDED_MODE = 'excluded';

    public function up(): void
    {
        if (! Schema::hasColumn('transactions', 'equipment_logger_mode')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('equipment_logger_mode')
                    ->nullable()
                    ->after('project_code');
            });
        }

        $legacyItemModes = [];

        if (Schema::hasColumn('items', 'equipment_logger_mode')) {
            $legacyItemModes = DB::table('items')
                ->pluck('equipment_logger_mode', 'id')
                ->toArray();
        }

        DB::table('transactions')
            ->select('id', 'item_id', 'transac_type', 'equipment_logger_mode')
            ->orderBy('created_at')
            ->orderBy('id')
            ->chunk(500, function ($transactions) use ($legacyItemModes) {
                foreach ($transactions as $transaction) {
                    $resolvedMode = $transaction->equipment_logger_mode;

                    if ($transaction->transac_type === Inventory::INCOMING->value) {
                        $resolvedMode = $legacyItemModes[$transaction->item_id] ?? self::DEFAULT_MODE;
                    }

                    DB::table('transactions')
                        ->where('id', $transaction->id)
                        ->update([
                            'equipment_logger_mode' => $resolvedMode,
                        ]);
                }
            });

        if (Schema::hasColumn('items', 'equipment_logger_mode')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('equipment_logger_mode');
            });
        }

        $this->upsertEquipmentLoggerModesOption();
    }

    public function down(): void
    {
        if (! Schema::hasColumn('items', 'equipment_logger_mode')) {
            Schema::table('items', function (Blueprint $table) {
                $table->string('equipment_logger_mode')
                    ->default(self::EXCLUDED_MODE)
                    ->after('supplier_id');
            });
        }

        $latestIncomingModesByItem = DB::table('transactions')
            ->select('item_id', 'equipment_logger_mode', 'created_at', 'id')
            ->where('transac_type', Inventory::INCOMING->value)
            ->whereNotNull('equipment_logger_mode')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->unique('item_id')
            ->pluck('equipment_logger_mode', 'item_id')
            ->toArray();

        DB::table('items')
            ->select('id')
            ->orderBy('id')
            ->chunk(500, function ($items) use ($latestIncomingModesByItem) {
                foreach ($items as $item) {
                    DB::table('items')
                        ->where('id', $item->id)
                        ->update([
                            'equipment_logger_mode' => $latestIncomingModesByItem[$item->id] ?? self::EXCLUDED_MODE,
                        ]);
                }
            });

        if (Schema::hasColumn('transactions', 'equipment_logger_mode')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('equipment_logger_mode');
            });
        }

        DB::table('options')
            ->where('key', self::OPTION_KEY)
            ->delete();
    }

    private function upsertEquipmentLoggerModesOption(): void
    {
        $now = now();
        $existing = DB::table('options')
            ->where('key', self::OPTION_KEY)
            ->first();

        $payload = [
            'value' => self::DEFAULT_MODE,
            'label' => 'Equipment Logger Modes',
            'description' => 'Defines the stored equipment/supply sharing modes used by incoming inventory transactions and the equipment logger.',
            'type' => 'select',
            'group' => 'inventory',
            'options' => json_encode([
                ['value' => 'borrowable', 'label' => 'Borrowable / Common-use'],
                ['value' => 'tracked_only', 'label' => 'Tracked only / Not borrowable'],
                ['value' => 'excluded', 'label' => 'Excluded from logger'],
            ]),
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('options')
                ->where('id', $existing->id)
                ->update($payload);

            return;
        }

        DB::table('options')->insert($payload + [
            'id' => (string) Str::uuid(),
            'key' => self::OPTION_KEY,
            'created_at' => $now,
        ]);
    }
};


