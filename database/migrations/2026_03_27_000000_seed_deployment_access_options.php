<?php

use App\Services\DeploymentAccessService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach (DeploymentAccessService::optionDefinitions() as $key => $definition) {
            $existing = DB::table('options')->where('key', $key)->first();

            $payload = [
                'value' => $definition['default'],
                'label' => $definition['label'],
                'description' => $definition['description'],
                'type' => $definition['type'],
                'group' => $definition['group'],
                'options' => json_encode($definition['options']),
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('options')
                    ->where('id', $existing->id)
                    ->update($payload);

                continue;
            }

            DB::table('options')->insert($payload + [
                'id' => (string) Str::uuid(),
                'key' => $key,
                'created_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('options')
            ->whereIn('key', array_keys(DeploymentAccessService::optionDefinitions()))
            ->delete();
    }
};