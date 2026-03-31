<?php

namespace Tests\Feature\Deployment;

use App\Services\DeploymentAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class DeploymentAccessOptionsApiTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.url', 'https://onecbc.philrice.gov.ph');
        config()->set('app.local_url', 'http://192.168.36.10');
    }

    public function test_admin_can_fetch_deployment_access_settings(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $this->getJson(route('api.options.deployment-access'))
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath(
                'data.modules.' . DeploymentAccessService::MODULE_EQUIPMENT_LOGGER . '.access',
                DeploymentAccessService::ACCESS_LOCAL,
            )
            ->assertJsonPath(
                'data.modules.' . DeploymentAccessService::MODULE_EQUIPMENT_LOGGER . '.mode',
                DeploymentAccessService::MODE_ACTIVE,
            )
            ->assertJsonPath('data.sections.0.key', 'guest')
            ->assertJsonPath('data.sections.1.key', 'internal')
            ->assertJsonCount(7, 'data.sections.0.items')
            ->assertJsonCount(4, 'data.sections.1.items');
    }

    public function test_admin_can_update_deployment_access_settings(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $payload = [
            'modules' => [
                DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT => [
                    'access' => DeploymentAccessService::ACCESS_BOTH,
                    'mode' => DeploymentAccessService::MODE_MAINTENANCE,
                ],
                DeploymentAccessService::MODULE_OPTIONS => [
                    'access' => DeploymentAccessService::ACCESS_INTERNET,
                    'mode' => DeploymentAccessService::MODE_MAINTENANCE,
                ],
            ],
        ];

        $this->putJson(route('api.options.deployment-access.update'), $payload)
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath(
                'data.modules.' . DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT . '.access',
                DeploymentAccessService::ACCESS_BOTH,
            )
            ->assertJsonPath(
                'data.modules.' . DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT . '.mode',
                DeploymentAccessService::MODE_MAINTENANCE,
            )
            ->assertJsonPath(
                'data.modules.' . DeploymentAccessService::MODULE_OPTIONS . '.access',
                DeploymentAccessService::ACCESS_INTERNET,
            )
            ->assertJsonPath(
                'data.modules.' . DeploymentAccessService::MODULE_OPTIONS . '.mode',
                DeploymentAccessService::MODE_MAINTENANCE,
            );

        $this->assertDatabaseHas('options', [
            'key' => DeploymentAccessService::accessOptionKey(DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT),
            'value' => DeploymentAccessService::ACCESS_BOTH,
            'group' => DeploymentAccessService::OPTION_GROUP,
        ]);

        $this->assertDatabaseHas('options', [
            'key' => DeploymentAccessService::modeOptionKey(DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT),
            'value' => DeploymentAccessService::MODE_MAINTENANCE,
            'group' => DeploymentAccessService::OPTION_GROUP,
        ]);

        $this->assertDatabaseHas('options', [
            'key' => DeploymentAccessService::accessOptionKey(DeploymentAccessService::MODULE_OPTIONS),
            'value' => DeploymentAccessService::ACCESS_INTERNET,
            'group' => DeploymentAccessService::OPTION_GROUP,
        ]);
    }

    public function test_admin_cannot_deactivate_protected_options_module(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $this->putJson(route('api.options.deployment-access.update'), [
            'modules' => [
                DeploymentAccessService::MODULE_OPTIONS => [
                    'access' => DeploymentAccessService::ACCESS_BOTH,
                    'mode' => DeploymentAccessService::MODE_DEACTIVATED,
                ],
            ],
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Protected modules cannot be deactivated from the UI.')
            ->assertJsonPath('invalid_keys.0', DeploymentAccessService::MODULE_OPTIONS);
    }

    public function test_deployment_access_update_rejects_unknown_keys(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $this->putJson(route('api.options.deployment-access.update'), [
            'modules' => [
                'unknown_key' => [
                    'access' => DeploymentAccessService::ACCESS_LOCAL,
                    'mode' => DeploymentAccessService::MODE_ACTIVE,
                ],
            ],
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Unknown module keys provided.');
    }

    public function test_deployment_access_endpoints_require_authentication(): void
    {
        $this->getJson(route('api.options.deployment-access'))
            ->assertUnauthorized();

        $this->putJson(route('api.options.deployment-access.update'), [
            'modules' => [
                DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT => [
                    'access' => DeploymentAccessService::ACCESS_BOTH,
                    'mode' => DeploymentAccessService::MODE_ACTIVE,
                ],
            ],
        ])
            ->assertUnauthorized();
    }

    public function test_deployment_access_endpoints_reject_non_admin_users_without_permission(): void
    {
        Sanctum::actingAs($this->createUserWithRole('researcher'));

        $this->getJson(route('api.options.deployment-access'))
            ->assertForbidden();

        $this->putJson(route('api.options.deployment-access.update'), [
            'modules' => [
                DeploymentAccessService::MODULE_SUPPLIES_CHECKOUT => [
                    'access' => DeploymentAccessService::ACCESS_BOTH,
                    'mode' => DeploymentAccessService::MODE_ACTIVE,
                ],
            ],
        ])
            ->assertForbidden();
    }
}
