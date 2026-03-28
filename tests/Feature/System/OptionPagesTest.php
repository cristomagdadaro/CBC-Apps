<?php

namespace Tests\Feature\System;

use App\Models\Option;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use Tests\WithTestRoles;

class OptionPagesTest extends TestCase
{
    use RefreshDatabase;
    use WithTestRoles;

    public function test_admin_can_view_option_create_page(): void
    {
        $this->actingAs($this->createAdminUser());

        $this->get(route('system.options.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('System/Options/OptionUpsert')
            );
    }

    public function test_admin_can_view_option_edit_page_with_existing_option(): void
    {
        $this->actingAs($this->createAdminUser());

        $option = Option::query()->create([
            'key' => 'ui_option',
            'value' => 'sample',
            'label' => 'UI Option',
            'description' => 'Used to verify the edit page render path.',
            'type' => 'text',
            'group' => 'system',
            'options' => null,
        ]);

        $this->get(route('system.options.show', ['id' => $option->id]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('System/Options/OptionUpsert')
                ->where('data.id', $option->id)
                ->where('data.key', 'ui_option')
                ->where('data.label', 'UI Option')
            );
    }

    public function test_non_admin_user_cannot_view_option_upsert_pages(): void
    {
        $this->actingAs($this->createUserWithRole('researcher'));

        $option = Option::query()->create([
            'key' => 'restricted_option',
            'value' => 'sample',
            'label' => 'Restricted Option',
            'description' => 'Used to verify access control.',
            'type' => 'text',
            'group' => 'system',
            'options' => null,
        ]);

        $this->get(route('system.options.create'))->assertForbidden();
        $this->get(route('system.options.show', ['id' => $option->id]))->assertForbidden();
    }
}