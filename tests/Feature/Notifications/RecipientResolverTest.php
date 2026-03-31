<?php

namespace Tests\Feature\Notifications;

use App\Models\Option;
use App\Models\Role;
use App\Models\User;
use App\Services\Notifications\RecipientResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipientResolverTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_resolves_unique_emails_from_user_backed_options_and_roles(): void
    {
        $selectedUser = User::factory()->create(['email' => 'ops@example.com']);

        Option::factory()->create([
            'key' => 'inventory_checkout_notification_emails',
            'value' => json_encode([
                ['user_id' => $selectedUser->id],
                ['email' => 'OPS@example.com'],
                ['email' => 'missing@example.com'],
            ]),
            'group' => 'notifications',
        ]);

        $role = Role::query()->firstOrCreate(
            ['name' => 'ict_manager'],
            [
                'label' => 'ICT Manager',
                'description' => 'ICT Manager',
            ],
        );

        $manager = User::factory()->create(['email' => 'manager@example.com']);
        $manager->roles()->attach($role);

        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'is_admin' => true,
        ]);

        $emails = app(RecipientResolver::class)->resolve('inventory.checkout');

        $this->assertEqualsCanonicalizing([
            'ops@example.com',
            'manager@example.com',
            'admin@example.com',
        ], $emails);
    }
}
