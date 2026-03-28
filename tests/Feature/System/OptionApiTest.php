<?php

namespace Tests\Feature\System;

use App\Models\Option;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Tests\WithTestRoles;

class OptionApiTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    public function test_admin_cannot_delete_options_via_api(): void
    {
        Sanctum::actingAs($this->createAdminUser());

        $option = Option::query()->create([
            'key' => 'test_option',
            'value' => 'example',
            'label' => 'Test Option',
            'description' => 'Used to verify delete protection.',
            'type' => 'text',
            'group' => 'system',
            'options' => null,
        ]);

        $this->deleteJson(route('api.options.destroy', ['id' => $option->id]))
            ->assertForbidden()
            ->assertJsonPath('message', 'Option deletion is disabled.');

        $this->assertDatabaseHas('options', [
            'id' => $option->id,
            'key' => 'test_option',
        ]);
    }
}