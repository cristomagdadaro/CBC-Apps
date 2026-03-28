<?php

use App\Services\DeploymentAccessService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    private const LEGACY_KEYS = [
        'route_access_web_equipment_logger',
        'route_access_web_supplies_checkout',
        'route_access_web_laboratory_dashboard',
        'route_access_api_equipment_logger',
        'route_access_api_supplies_checkout',
        'route_access_api_laboratory_dashboard',
    ];

    public function up(): void
    {
        $now = now();
        $legacyValues = DB::table('options')
            ->whereIn('key', self::LEGACY_KEYS)
            ->pluck('value', 'key')
            ->toArray();

        $moduleDefinitions = DeploymentAccessService::moduleDefinitions();
        $optionDefinitions = DeploymentAccessService::optionDefinitions();

        $legacyAccess = [
            DeploymentAccessService::MODULE_EQUIPMENT_LOGGER => $legacyValues['route_access_web_equipment_logger'] ?? $legacyValues['route_access_api_equipment_logger'] ?? null,
            DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT => $legacyValues['route_access_web_supplies_checkout'] ?? $legacyValues['route_access_api_supplies_checkout'] ?? null,
            DeploymentAccessService::MODULE_LABORATORY_DASHBOARD => $legacyValues['route_access_web_laboratory_dashboard'] ?? $legacyValues['route_access_api_laboratory_dashboard'] ?? null,
        ];

        foreach ($moduleDefinitions as $module => $definition) {
            $accessKey = DeploymentAccessService::accessOptionKey($module);
            $modeKey = DeploymentAccessService::modeOptionKey($module);

            $this->upsertOption(
                $accessKey,
                $legacyAccess[$module] ?? $definition['default_access'],
                $optionDefinitions[$accessKey],
                $now,
            );

            $this->upsertOption(
                $modeKey,
                $definition['default_mode'],
                $optionDefinitions[$modeKey],
                $now,
            );
        }

        DB::table('options')
            ->whereIn('key', self::LEGACY_KEYS)
            ->delete();
    }

    public function down(): void
    {
        $now = now();
        $moduleAccess = DB::table('options')
            ->whereIn('key', [
                DeploymentAccessService::accessOptionKey(DeploymentAccessService::MODULE_EQUIPMENT_LOGGER),
                DeploymentAccessService::accessOptionKey(DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT),
                DeploymentAccessService::accessOptionKey(DeploymentAccessService::MODULE_LABORATORY_DASHBOARD),
            ])
            ->pluck('value', 'key')
            ->toArray();

        $legacyDefinitions = [
            'route_access_web_equipment_logger' => [
                'value' => $moduleAccess[DeploymentAccessService::accessOptionKey(DeploymentAccessService::MODULE_EQUIPMENT_LOGGER)] ?? DeploymentAccessService::ACCESS_LOCAL,
                'label' => 'Web: Equipment Logger Access',
                'description' => 'Legacy route-group setting for equipment logger web routes.',
            ],
            'route_access_api_equipment_logger' => [
                'value' => $moduleAccess[DeploymentAccessService::accessOptionKey(DeploymentAccessService::MODULE_EQUIPMENT_LOGGER)] ?? DeploymentAccessService::ACCESS_LOCAL,
                'label' => 'API: Equipment Logger Access',
                'description' => 'Legacy route-group setting for equipment logger API routes.',
            ],
            'route_access_web_supplies_checkout' => [
                'value' => $moduleAccess[DeploymentAccessService::accessOptionKey(DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT)] ?? DeploymentAccessService::ACCESS_LOCAL,
                'label' => 'Web: Supplies Checkout Access',
                'description' => 'Legacy route-group setting for supplies checkout web routes.',
            ],
            'route_access_api_supplies_checkout' => [
                'value' => $moduleAccess[DeploymentAccessService::accessOptionKey(DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT)] ?? DeploymentAccessService::ACCESS_LOCAL,
                'label' => 'API: Supplies Checkout Access',
                'description' => 'Legacy route-group setting for supplies checkout API routes.',
            ],
            'route_access_web_laboratory_dashboard' => [
                'value' => $moduleAccess[DeploymentAccessService::accessOptionKey(DeploymentAccessService::MODULE_LABORATORY_DASHBOARD)] ?? DeploymentAccessService::ACCESS_LOCAL,
                'label' => 'Web: Laboratory Dashboard Access',
                'description' => 'Legacy route-group setting for laboratory dashboard web routes.',
            ],
            'route_access_api_laboratory_dashboard' => [
                'value' => $moduleAccess[DeploymentAccessService::accessOptionKey(DeploymentAccessService::MODULE_LABORATORY_DASHBOARD)] ?? DeploymentAccessService::ACCESS_LOCAL,
                'label' => 'API: Laboratory Dashboard Access',
                'description' => 'Legacy route-group setting for laboratory dashboard API routes.',
            ],
        ];

        foreach ($legacyDefinitions as $key => $definition) {
            $this->upsertLegacyOption($key, $definition, $now);
        }

        DB::table('options')
            ->whereIn('key', array_keys(DeploymentAccessService::optionDefinitions()))
            ->delete();
    }

    private function upsertOption(string $key, string $value, array $definition, $now): void
    {
        $existing = DB::table('options')->where('key', $key)->first();

        $payload = [
            'value' => $value,
            'label' => $definition['label'],
            'description' => $definition['description'],
            'type' => $definition['type'],
            'group' => $definition['group'],
            'options' => json_encode($definition['options']),
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('options')->where('id', $existing->id)->update($payload);

            return;
        }

        DB::table('options')->insert($payload + [
            'id' => (string) Str::uuid(),
            'key' => $key,
            'created_at' => $now,
        ]);
    }

    private function upsertLegacyOption(string $key, array $definition, $now): void
    {
        $existing = DB::table('options')->where('key', $key)->first();

        $payload = [
            'value' => $definition['value'],
            'label' => $definition['label'],
            'description' => $definition['description'],
            'type' => 'select',
            'group' => DeploymentAccessService::OPTION_GROUP,
            'options' => json_encode([
                ['value' => DeploymentAccessService::ACCESS_LOCAL, 'label' => 'Local only'],
                ['value' => DeploymentAccessService::ACCESS_INTERNET, 'label' => 'Internet only'],
                ['value' => DeploymentAccessService::ACCESS_BOTH, 'label' => 'Both'],
            ]),
            'updated_at' => $now,
        ];

        if ($existing) {
            DB::table('options')->where('id', $existing->id)->update($payload);

            return;
        }

        DB::table('options')->insert($payload + [
            'id' => (string) Str::uuid(),
            'key' => $key,
            'created_at' => $now,
        ]);
    }
};