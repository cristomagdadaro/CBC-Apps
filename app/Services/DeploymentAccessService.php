<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\OptionRepo;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DeploymentAccessService
{
    public const ACCESS_LOCAL = 'local';
    public const ACCESS_INTERNET = 'internet';
    public const ACCESS_BOTH = 'both';

    public const MODE_ACTIVE = 'active';
    public const MODE_DEACTIVATED = 'deactivated';
    public const MODE_MAINTENANCE = 'maintenance';

    public const CHANNEL_LOCAL = 'local';
    public const CHANNEL_INTERNET = 'internet';

    public const OPTION_GROUP = 'deployment_access';

    public const MODULE_EQUIPMENT_LOGGER = 'equipment_logger';
    public const MODULE_SUPPLIES_CHECKOUT = 'supplies_checkout';
    public const MODULE_LABORATORY_DASHBOARD = 'laboratory_dashboard';
    public const MODULE_FORMS = 'forms';
    public const MODULE_FES = 'fes';
    public const MODULE_INCIDENT_REPORTS = 'incident_reports';
    public const MODULE_INVENTORY = 'inventory';
    public const MODULE_RENTALS = 'rentals';
    public const MODULE_OPTIONS = 'options';
    public const MODULE_EXPERIMENT_MONITORING = 'experiment_monitoring';
    public const MODULE_RESEARCH = 'research';
    public const MODULE_GOLINK = 'golink';

    private const LOCAL_FALLBACK_HOSTS = [
        '127.0.0.1',
        'localhost',
        '::1',
    ];

    private const SECTION_LABELS = [
        'guest' => 'Guest And Shared Modules',
        'internal' => 'Internal Modules',
    ];

    private ?array $moduleConfigCache = null;
    private ?array $managementPayloadCache = null;
    private array $requestChannelCache = [];
    private array $requestAdminBypassCache = [];
    private array $requestEvaluationCache = [];
    private array $requestModuleStateCache = [];
    private array $requestSharedPayloadCache = [];

    public function __construct(private readonly OptionRepo $optionRepo)
    {
    }

    public static function moduleDefinitions(): array
    {
        return [
            self::MODULE_EQUIPMENT_LOGGER => [
                'label' => 'Equipment Logger',
                'description' => 'Controls the public laboratory and ICT equipment logger pages together with their related APIs.',
                'default_access' => self::ACCESS_LOCAL,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'guest',
                'allows_deactivation' => true,
            ],
            self::MODULE_SUPPLIES_CHECKOUT => [
                'label' => 'Supplies Checkout',
                'description' => 'Controls the public supplies checkout page and its related APIs.',
                'default_access' => self::ACCESS_LOCAL,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'guest',
                'allows_deactivation' => true,
            ],
            self::MODULE_FORMS => [
                'label' => 'Forms',
                'description' => 'Controls public and authenticated event form pages together with the forms API module.',
                'default_access' => self::ACCESS_BOTH,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'guest',
                'allows_deactivation' => true,
            ],
            self::MODULE_FES => [
                'label' => 'FES Requests',
                'description' => 'Controls request-to-use pages, guest request submission, and FES approval APIs.',
                'default_access' => self::ACCESS_BOTH,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'guest',
                'allows_deactivation' => true,
            ],
            self::MODULE_INCIDENT_REPORTS => [
                'label' => 'Incident Reports',
                'description' => 'Controls the guest incident-report form and its related public submission API.',
                'default_access' => self::ACCESS_BOTH,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'guest',
                'allows_deactivation' => true,
            ],
            self::MODULE_RENTALS => [
                'label' => 'Rentals',
                'description' => 'Controls guest and authenticated rental pages together with the rentals API module.',
                'default_access' => self::ACCESS_BOTH,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'guest',
                'allows_deactivation' => true,
            ],
            self::MODULE_EXPERIMENT_MONITORING => [
                'label' => 'Experiment Monitoring',
                'description' => 'Controls the guest laboratory experiment monitoring page.',
                'default_access' => self::ACCESS_BOTH,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'guest',
                'allows_deactivation' => true,
            ],
            self::MODULE_LABORATORY_DASHBOARD => [
                'label' => 'Laboratory Dashboard',
                'description' => 'Controls the authenticated laboratory dashboard and its related API endpoints.',
                'default_access' => self::ACCESS_LOCAL,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'internal',
                'allows_deactivation' => true,
            ],
            self::MODULE_INVENTORY => [
                'label' => 'Inventory',
                'description' => 'Controls inventory management pages, incident reports, and the inventory API module outside supplies checkout.',
                'default_access' => self::ACCESS_BOTH,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'internal',
                'allows_deactivation' => true,
            ],
            self::MODULE_OPTIONS => [
                'label' => 'Options',
                'description' => 'Controls the system options pages and the options API module.',
                'default_access' => self::ACCESS_BOTH,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'internal',
                'allows_deactivation' => false,
            ],
            self::MODULE_RESEARCH => [
                'label' => 'Research',
                'description' => 'Controls research pages, experiment monitoring pages, and the research API module.',
                'default_access' => self::ACCESS_BOTH,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'internal',
                'allows_deactivation' => true,
            ],
            self::MODULE_GOLINK => [
                'label' => 'Go Link',
                'description' => 'Controls Go Link management pages, API endpoints, and public redirect pages.',
                'default_access' => self::ACCESS_BOTH,
                'default_mode' => self::MODE_ACTIVE,
                'section' => 'internal',
                'allows_deactivation' => true,
            ],
        ];
    }

    public static function optionDefinitions(): array
    {
        return collect(self::moduleDefinitions())
            ->flatMap(function (array $definition, string $module) {
                return [
                    self::accessOptionKey($module) => [
                        'module' => $module,
                        'kind' => 'access',
                        'default' => $definition['default_access'],
                        'label' => $definition['label'] . ' Deployment Access',
                        'description' => 'Controls whether the ' . strtolower($definition['label']) . ' module is available on the local deployment, the internet deployment, or both.',
                        'type' => 'select',
                        'group' => self::OPTION_GROUP,
                        'options' => self::accessOptions(),
                    ],
                    self::modeOptionKey($module) => [
                        'module' => $module,
                        'kind' => 'mode',
                        'default' => $definition['default_mode'],
                        'label' => $definition['label'] . ' Mode',
                        'description' => 'Controls whether the ' . strtolower($definition['label']) . ' module is active, deactivated, or read-only during maintenance.',
                        'type' => 'select',
                        'group' => self::OPTION_GROUP,
                        'options' => self::modeOptions(),
                    ],
                ];
            })
            ->toArray();
    }

    public static function accessOptionKey(string $module): string
    {
        return 'module_access_' . $module;
    }

    public static function modeOptionKey(string $module): string
    {
        return 'module_mode_' . $module;
    }

    public function currentChannel(Request $request): string
    {
        $requestKey = $this->requestCacheKey($request);

        if (array_key_exists($requestKey, $this->requestChannelCache)) {
            return $this->requestChannelCache[$requestKey];
        }

        $host = strtolower($request->getHost());

        $localHost = $this->configuredHost(config('app.local_url'));
        $internetHost = $this->configuredHost(config('app.url'));

        if ($localHost !== null && $host === $localHost) {
            return $this->requestChannelCache[$requestKey] = self::CHANNEL_LOCAL;
        }

        if ($internetHost !== null && $host === $internetHost) {
            return $this->requestChannelCache[$requestKey] = self::CHANNEL_INTERNET;
        }

        if (in_array($host, self::LOCAL_FALLBACK_HOSTS, true)) {
            return $this->requestChannelCache[$requestKey] = self::CHANNEL_LOCAL;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $isPublicIp = filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);

            return $this->requestChannelCache[$requestKey] = ($isPublicIp === false ? self::CHANNEL_LOCAL : self::CHANNEL_INTERNET);
        }

        return $this->requestChannelCache[$requestKey] = self::CHANNEL_INTERNET;
    }

    public function accessFor(string $module): string
    {
        return $this->moduleConfigMap()[$module]['access'] ?? self::ACCESS_BOTH;
    }

    public function modeFor(string $module): string
    {
        return $this->moduleConfigMap()[$module]['mode'] ?? self::MODE_ACTIVE;
    }

    public function evaluate(Request $request, string $module): array
    {
        $cacheKey = $this->requestCacheKey($request) . ':' . $module;

        if (array_key_exists($cacheKey, $this->requestEvaluationCache)) {
            return $this->requestEvaluationCache[$cacheKey];
        }

        $channel = $this->currentChannel($request);
        $access = $this->accessFor($module);
        $mode = $this->modeFor($module);
        $adminBypass = $this->hasAdministratorBypass($request);

        if ($adminBypass) {
            return $this->requestEvaluationCache[$cacheKey] = [
                'allowed' => true,
                'reason' => null,
                'message' => null,
                'status' => Response::HTTP_OK,
                'channel' => $channel,
                'access' => $access,
                'mode' => $mode,
                'read_only' => false,
                'admin_bypass' => true,
            ];
        }

        if (! $this->allowsChannel($channel, $access)) {
            return $this->requestEvaluationCache[$cacheKey] = [
                'allowed' => false,
                'reason' => 'deployment_access',
                'message' => $this->accessMessage($module),
                'status' => Response::HTTP_FORBIDDEN,
                'channel' => $channel,
                'access' => $access,
                'mode' => $mode,
            ];
        }

        if ($mode === self::MODE_DEACTIVATED) {
            return $this->requestEvaluationCache[$cacheKey] = [
                'allowed' => false,
                'reason' => 'deactivated',
                'message' => 'This module is currently deactivated.',
                'status' => Response::HTTP_SERVICE_UNAVAILABLE,
                'channel' => $channel,
                'access' => $access,
                'mode' => $mode,
            ];
        }

        if ($mode === self::MODE_MAINTENANCE && ! $this->isReadOnlyRequest($request)) {
            return $this->requestEvaluationCache[$cacheKey] = [
                'allowed' => false,
                'reason' => 'maintenance',
                'message' => 'This module is currently in maintenance mode. Read-only access is still available.',
                'status' => Response::HTTP_SERVICE_UNAVAILABLE,
                'channel' => $channel,
                'access' => $access,
                'mode' => $mode,
            ];
        }

        return $this->requestEvaluationCache[$cacheKey] = [
            'allowed' => true,
            'reason' => null,
            'message' => null,
            'status' => Response::HTTP_OK,
            'channel' => $channel,
            'access' => $access,
            'mode' => $mode,
            'read_only' => $mode === self::MODE_MAINTENANCE,
            'admin_bypass' => false,
        ];
    }

    public function allows(Request $request, string $module): bool
    {
        return $this->evaluate($request, $module)['allowed'] === true;
    }

    public function isVisibleOnWelcome(Request $request, string $module): bool
    {
        if ($this->hasAdministratorBypass($request)) {
            return true;
        }

        $channel = $this->currentChannel($request);
        $config = $this->moduleConfigMap()[$module] ?? [
            'access' => self::ACCESS_BOTH,
            'mode' => self::MODE_ACTIVE,
        ];

        return $this->allowsChannel($channel, $config['access'])
            && $config['mode'] !== self::MODE_DEACTIVATED;
    }

    public function sharedPayload(Request $request): array
    {
        $requestKey = $this->requestCacheKey($request);

        if (array_key_exists($requestKey, $this->requestSharedPayloadCache)) {
            return $this->requestSharedPayloadCache[$requestKey];
        }

        $channel = $this->currentChannel($request);
        $modules = $this->moduleStateMap($request);
        $adminBypass = $this->hasAdministratorBypass($request);

        return $this->requestSharedPayloadCache[$requestKey] = [
            'channel' => $channel,
            'admin_bypass' => $adminBypass,
            'local_url' => (string) config('app.local_url'),
            'internet_url' => (string) config('app.url'),
            'modules' => $modules,
            'services' => collect($modules)
                ->mapWithKeys(fn (array $state, string $module) => [$module => $state['visible'] ?? false])
                ->toArray(),
        ];
    }

    public function accessMessage(string $module): string
    {
        return match ($this->accessFor($module)) {
            self::ACCESS_LOCAL => 'This feature is only available on the local deployment.',
            self::ACCESS_INTERNET => 'This feature is only available on the internet deployment.',
            default => 'This feature is available on both deployments.',
        };
    }

    public function managementPayload(): array
    {
        if ($this->managementPayloadCache !== null) {
            return $this->managementPayloadCache;
        }

        $definitions = self::moduleDefinitions();
        $modules = $this->moduleConfigMap();

        $sections = collect(self::SECTION_LABELS)->map(function (string $label, string $section) use ($definitions, $modules) {
            return [
                'key' => $section,
                'label' => $label,
                'items' => collect($definitions)
                    ->filter(fn (array $definition) => ($definition['section'] ?? null) === $section)
                    ->map(function (array $definition, string $module) use ($modules) {
                        return [
                            'module' => $module,
                            'label' => $definition['label'],
                            'description' => $definition['description'],
                            'access' => $modules[$module]['access'] ?? ($definition['default_access'] ?? self::ACCESS_BOTH),
                            'mode' => $modules[$module]['mode'] ?? ($definition['default_mode'] ?? self::MODE_ACTIVE),
                            'allows_deactivation' => $definition['allows_deactivation'] ?? true,
                            'access_options' => self::accessOptions(),
                            'mode_options' => self::modeOptions(),
                        ];
                    })
                    ->values()
                    ->all(),
            ];
        })->values()->all();

        return $this->managementPayloadCache = [
            'modules' => $modules,
            'sections' => $sections,
            'access_choices' => self::accessOptions(),
            'mode_choices' => self::modeOptions(),
            'local_url' => (string) config('app.local_url'),
            'internet_url' => (string) config('app.url'),
        ];
    }

    public function updateModules(array $modules): array
    {
        $definitions = self::moduleDefinitions();
        $optionDefinitions = self::optionDefinitions();

        foreach ($modules as $module => $settings) {
            if (! isset($definitions[$module])) {
                continue;
            }

            $this->optionRepo->upsertOption(
                self::accessOptionKey($module),
                $settings['access'],
                $optionDefinitions[self::accessOptionKey($module)],
            );

            $this->optionRepo->upsertOption(
                self::modeOptionKey($module),
                $settings['mode'],
                $optionDefinitions[self::modeOptionKey($module)],
            );
        }

        $this->flushRuntimeCaches();

        return $this->managementPayload();
    }

    public static function validAccessValues(): array
    {
        return [self::ACCESS_LOCAL, self::ACCESS_INTERNET, self::ACCESS_BOTH];
    }

    public static function validModeValues(): array
    {
        return [self::MODE_ACTIVE, self::MODE_DEACTIVATED, self::MODE_MAINTENANCE];
    }

    public static function validModuleKeys(): array
    {
        return array_keys(self::moduleDefinitions());
    }

    public static function canBeDeactivated(string $module): bool
    {
        return self::moduleDefinitions()[$module]['allows_deactivation'] ?? true;
    }

    private function normalizeAccess(mixed $value, string $default): string
    {
        if (! is_string($value)) {
            return $default;
        }

        $normalized = strtolower(trim($value));

        return in_array($normalized, [self::ACCESS_LOCAL, self::ACCESS_INTERNET, self::ACCESS_BOTH], true)
            ? $normalized
            : $default;
    }

    private function normalizeMode(mixed $value, string $default): string
    {
        if (! is_string($value)) {
            return $default;
        }

        $normalized = strtolower(trim($value));

        return in_array($normalized, self::validModeValues(), true)
            ? $normalized
            : $default;
    }

    private function moduleConfigMap(): array
    {
        if ($this->moduleConfigCache !== null) {
            return $this->moduleConfigCache;
        }

        $definitions = self::moduleDefinitions();
        $values = $this->optionRepo->getValuesByKeys(array_keys(self::optionDefinitions()));

        return $this->moduleConfigCache = collect($definitions)
            ->mapWithKeys(function (array $definition, string $module) use ($values) {
                return [
                    $module => [
                        'access' => $this->normalizeAccess(
                            $values[self::accessOptionKey($module)] ?? null,
                            $definition['default_access'] ?? self::ACCESS_BOTH,
                        ),
                        'mode' => $this->normalizeMode(
                            $values[self::modeOptionKey($module)] ?? null,
                            $definition['default_mode'] ?? self::MODE_ACTIVE,
                        ),
                    ],
                ];
            })
            ->toArray();
    }

    private function moduleStateMap(Request $request): array
    {
        $requestKey = $this->requestCacheKey($request);

        if (array_key_exists($requestKey, $this->requestModuleStateCache)) {
            return $this->requestModuleStateCache[$requestKey];
        }

        $adminBypass = $this->hasAdministratorBypass($request);
        $channel = $this->currentChannel($request);

        return $this->requestModuleStateCache[$requestKey] = collect($this->moduleConfigMap())
            ->mapWithKeys(function (array $settings, string $module) use ($request, $adminBypass, $channel) {
                $visible = $this->visibleForState($channel, $settings['access'], $settings['mode'], $adminBypass);
                $available = $this->availableForState($request, $channel, $settings['access'], $settings['mode'], $adminBypass);

                return [
                    $module => $settings + [
                        'available' => $available,
                        'visible' => $visible,
                        'admin_bypass' => $adminBypass,
                    ],
                ];
            })
            ->toArray();
    }

    private function visibleForState(string $channel, string $access, string $mode, bool $adminBypass): bool
    {
        if ($adminBypass) {
            return true;
        }

        return $this->allowsChannel($channel, $access)
            && $mode !== self::MODE_DEACTIVATED;
    }

    private function availableForState(Request $request, string $channel, string $access, string $mode, bool $adminBypass): bool
    {
        if ($adminBypass) {
            return true;
        }

        if (! $this->allowsChannel($channel, $access)) {
            return false;
        }

        if ($mode === self::MODE_DEACTIVATED) {
            return false;
        }

        if ($mode === self::MODE_MAINTENANCE && ! $this->isReadOnlyRequest($request)) {
            return false;
        }

        return true;
    }

    private function allowsChannel(string $channel, string $requiredAccess): bool
    {
        if ($requiredAccess === self::ACCESS_BOTH) {
            return true;
        }

        return $requiredAccess === $channel;
    }

    private function configuredHost(mixed $url): ?string
    {
        if (! is_string($url) || trim($url) === '') {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) ? strtolower($host) : null;
    }

    private function isReadOnlyRequest(Request $request): bool
    {
        return in_array($request->getMethod(), ['GET', 'HEAD', 'OPTIONS'], true);
    }

    private function hasAdministratorBypass(Request $request): bool
    {
        $requestKey = $this->requestCacheKey($request);

        if (array_key_exists($requestKey, $this->requestAdminBypassCache)) {
            return $this->requestAdminBypassCache[$requestKey];
        }

        $user = $request->user();

        if (! $user instanceof User) {
            return $this->requestAdminBypassCache[$requestKey] = false;
        }

        return $this->requestAdminBypassCache[$requestKey] = ((bool) $user->is_admin || $user->hasRole('admin'));
    }

    private function requestCacheKey(Request $request): string
    {
        return (string) spl_object_id($request);
    }

    private function flushRuntimeCaches(): void
    {
        $this->moduleConfigCache = null;
        $this->managementPayloadCache = null;
        $this->requestChannelCache = [];
        $this->requestAdminBypassCache = [];
        $this->requestEvaluationCache = [];
        $this->requestModuleStateCache = [];
        $this->requestSharedPayloadCache = [];
    }

    private static function accessOptions(): array
    {
        return [
            ['value' => self::ACCESS_LOCAL, 'label' => 'Local only'],
            ['value' => self::ACCESS_INTERNET, 'label' => 'Internet only'],
            ['value' => self::ACCESS_BOTH, 'label' => 'Both'],
        ];
    }

    private static function modeOptions(): array
    {
        return [
            ['value' => self::MODE_ACTIVE, 'label' => 'Active'],
            ['value' => self::MODE_DEACTIVATED, 'label' => 'Deactivated'],
            ['value' => self::MODE_MAINTENANCE, 'label' => 'Maintenance'],
        ];
    }
}
