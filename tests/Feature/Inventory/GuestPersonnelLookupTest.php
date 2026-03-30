<?php

namespace Tests\Feature\Inventory;

use App\Models\Personnel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestPersonnelLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_personnel_lookup_returns_sanitized_exact_match_only(): void
    {
        Personnel::query()->create([
            'fname' => 'Jane',
            'mname' => 'Q',
            'lname' => 'Public',
            'suffix' => 'Jr.',
            'position' => 'Science Research Specialist',
            'phone' => '09171234567',
            'address' => 'Science City of Mu?oz',
            'email' => 'jane.public@example.test',
            'employee_id' => 'EMP-1001',
        ]);

        $this->getJson(route('api.inventory.personnels.index.guest', ['search' => 'EMP-1001']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.fullName', 'Jane Q Public Jr.')
            ->assertJsonPath('data.0.position', 'Science Research Specialist')
            ->assertJsonMissingPath('data.0.employee_id')
            ->assertJsonMissingPath('data.0.phone')
            ->assertJsonMissingPath('data.0.email')
            ->assertJsonMissingPath('data.0.address');
    }

    public function test_guest_personnel_lookup_returns_empty_data_when_search_is_missing(): void
    {
        Personnel::query()->create([
            'fname' => 'Jane',
            'lname' => 'Public',
            'position' => 'Science Research Specialist',
            'employee_id' => 'EMP-1001',
        ]);

        $this->getJson(route('api.inventory.personnels.index.guest'))
            ->assertOk()
            ->assertExactJson(['data' => []]);
    }
}
