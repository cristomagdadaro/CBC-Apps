<?php

namespace Tests\Feature\Deployment;

use App\Models\Option;
use App\Services\DeploymentAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class DeploymentAccessTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://onecbc.philrice.gov.ph');
        config()->set('app.local_url', 'http://192.168.36.10');

        Route::middleware(['web', 'deployment.access:' . DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT])
            ->get('/__test/deployment/web-supplies', fn () => response('ok', 200));

        Route::middleware(['web', 'deployment.access:' . DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT])
            ->post('/__test/deployment/web-supplies', fn () => response('ok', 200));

        Route::middleware(['web', 'deployment.access:' . DeploymentAccessService::MODULE_LABORATORY_DASHBOARD])
            ->get('/__test/deployment/web-laboratory-dashboard', fn () => response('ok', 200));

        Route::middleware(['api', 'deployment.access:' . DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT])
            ->get('/__test/deployment/api-supplies', fn () => response()->json(['ok' => true]));

        Route::middleware(['api', 'deployment.access:' . DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT])
            ->post('/__test/deployment/api-supplies', fn () => response()->json(['ok' => true]));

        Route::middleware(['web', 'deployment.access:' . DeploymentAccessService::MODULE_FORMS])
            ->get('/__test/deployment/web-forms', fn () => response('ok', 200));
    }

    public function test_internet_host_blocks_default_local_only_guest_routes(): void
    {
        $this->get('http://onecbc.philrice.gov.ph/inventory/outgoing')
            ->assertForbidden();

        $this->getJson('http://onecbc.philrice.gov.ph/api/guest/lab/equipments')
            ->assertForbidden()
            ->assertJsonPath('required_access', DeploymentAccessService::ACCESS_LOCAL);

        $this->get('http://onecbc.philrice.gov.ph/laboratory/equipments')
            ->assertForbidden();
    }

    public function test_local_host_allows_default_local_only_routes(): void
    {
        $this->get('http://192.168.36.10/__test/deployment/web-supplies')
            ->assertOk();

        $this->getJson('http://192.168.36.10/__test/deployment/api-supplies')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_option_model_can_allow_internet_access_for_guest_supplies_checkout(): void
    {
        $this->seedModuleSettings(
            DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT,
            DeploymentAccessService::ACCESS_BOTH,
            DeploymentAccessService::MODE_ACTIVE,
        );

        $this->get('http://onecbc.philrice.gov.ph/__test/deployment/web-supplies')
            ->assertOk();

        $this->getJson('http://onecbc.philrice.gov.ph/__test/deployment/api-supplies')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_option_model_can_make_laboratory_dashboard_internet_only(): void
    {
        $this->seedModuleSettings(
            DeploymentAccessService::MODULE_LABORATORY_DASHBOARD,
            DeploymentAccessService::ACCESS_INTERNET,
            DeploymentAccessService::MODE_ACTIVE,
        );

        $this->get('http://192.168.36.10/__test/deployment/web-laboratory-dashboard')
            ->assertForbidden();

        $this->get('http://onecbc.philrice.gov.ph/__test/deployment/web-laboratory-dashboard')
            ->assertOk();
    }

    public function test_module_maintenance_mode_allows_reads_but_blocks_writes(): void
    {
        $this->seedModuleSettings(
            DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT,
            DeploymentAccessService::ACCESS_BOTH,
            DeploymentAccessService::MODE_MAINTENANCE,
        );

        $this->get('http://onecbc.philrice.gov.ph/__test/deployment/web-supplies')
            ->assertOk();

        $this->postJson('http://onecbc.philrice.gov.ph/__test/deployment/api-supplies')
            ->assertStatus(503)
            ->assertJsonPath('reason', 'maintenance')
            ->assertJsonPath('mode', DeploymentAccessService::MODE_MAINTENANCE);
    }

    public function test_deactivated_module_blocks_all_requests(): void
    {
        $this->seedModuleSettings(
            DeploymentAccessService::MODULE_FORMS,
            DeploymentAccessService::ACCESS_BOTH,
            DeploymentAccessService::MODE_DEACTIVATED,
        );

        $this->get('http://onecbc.philrice.gov.ph/__test/deployment/web-forms')
            ->assertStatus(503);
    }

    public function test_welcome_page_marks_local_only_services_hidden_on_internet(): void
    {
        $this->get('http://onecbc.philrice.gov.ph/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('deployment_access.channel', DeploymentAccessService::CHANNEL_INTERNET)
                ->where('deployment_access.services.equipment_logger', false)
                ->where('deployment_access.services.forms', true)
                ->where('deployment_access.services.supplies_checkout', false)
            );
    }

    public function test_welcome_page_marks_local_only_services_visible_on_local_host(): void
    {
        $this->get('http://192.168.36.10/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('deployment_access.channel', DeploymentAccessService::CHANNEL_LOCAL)
                ->where('deployment_access.services.equipment_logger', true)
                ->where('deployment_access.services.rentals', true)
                ->where('deployment_access.services.supplies_checkout', true)
            );
    }

    public function test_welcome_page_reflects_option_override_for_internet_visibility(): void
    {
        $this->seedModuleSettings(
            DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT,
            DeploymentAccessService::ACCESS_BOTH,
            DeploymentAccessService::MODE_ACTIVE,
        );

        $this->get('http://onecbc.philrice.gov.ph/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('deployment_access.services.equipment_logger', false)
                ->where('deployment_access.services.supplies_checkout', true)
                ->where(
                    'deployment_access.modules.' . DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT . '.access',
                    DeploymentAccessService::ACCESS_BOTH,
                )
            );
    }

    public function test_welcome_page_loads_module_access_snapshot_once_per_request(): void
    {
        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->get('http://onecbc.philrice.gov.ph/')
            ->assertOk();

        $moduleAccessQueries = collect(DB::getQueryLog())
            ->pluck('query')
            ->filter(fn (string $query) => str_contains($query, 'from `options`') && str_contains($query, 'where `key` in'))
            ->values();

        DB::disableQueryLog();

        $this->assertCount(
            1,
            $moduleAccessQueries,
            'Deployment access options should be queried once per request.'
        );
    }

    public function test_admin_can_access_local_only_web_module_on_internet_host(): void
    {
        $this->actingAs($this->createAdminUser());

        $this->get('http://onecbc.philrice.gov.ph/__test/deployment/web-supplies')
            ->assertOk();
    }

    public function test_admin_can_access_local_only_api_module_on_internet_host(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $this->getJson('http://onecbc.philrice.gov.ph/__test/deployment/api-supplies')
            ->assertOk()
            ->assertJson(['ok' => true]);
    }

    public function test_admin_welcome_payload_keeps_local_only_services_visible_on_internet_host(): void
    {
        $this->actingAs($this->createAdminUser());

        $this->get('http://onecbc.philrice.gov.ph/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome')
                ->where('deployment_access.admin_bypass', true)
                ->where('deployment_access.services.equipment_logger', true)
                ->where('deployment_access.services.supplies_checkout', true)
            );
    }

    private function seedModuleSettings(string $module, string $access, string $mode): void
    {
        $definitions = DeploymentAccessService::optionDefinitions();
        $accessKey = DeploymentAccessService::accessOptionKey($module);
        $modeKey = DeploymentAccessService::modeOptionKey($module);

        Option::query()->updateOrCreate(
            ['key' => $accessKey],
            [
                'value' => $access,
                'label' => $definitions[$accessKey]['label'],
                'description' => $definitions[$accessKey]['description'],
                'type' => $definitions[$accessKey]['type'],
                'group' => $definitions[$accessKey]['group'],
                'options' => $definitions[$accessKey]['options'],
            ],
        );

        Option::query()->updateOrCreate(
            ['key' => $modeKey],
            [
                'value' => $mode,
                'label' => $definitions[$modeKey]['label'],
                'description' => $definitions[$modeKey]['description'],
                'type' => $definitions[$modeKey]['type'],
                'group' => $definitions[$modeKey]['group'],
                'options' => $definitions[$modeKey]['options'],
            ],
        );
    }
}
