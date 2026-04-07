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
        $personnel = Personnel::query()->create([
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
        $personnel->forceFill(['updated_at' => null])->saveQuietly();

        $this->getJson(route('api.inventory.personnels.index.guest', ['search' => 'EMP-1001']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.fullName', 'Jane Q Public Jr.')
            ->assertJsonPath('data.0.position', 'Science Research Specialist')
            ->assertJsonPath('data.0.phone', '09171234567')
            ->assertJsonPath('data.0.email', 'jane.public@example.test')
            ->assertJsonPath('data.0.address', 'Science City of Mu?oz')
            ->assertJsonPath('data.0.profile_requires_update', true)
            ->assertJsonPath('data.0.has_email', true)
            ->assertJsonMissingPath('data.0.employee_id');
    }

    public function test_guest_personnel_lookup_prefers_duplicate_record_with_email(): void
    {
        Personnel::query()->create([
            'fname' => 'Jane',
            'lname' => 'Duplicate',
            'position' => 'Technician',
            'employee_id' => 'EMP-DUPE-1',
            'email' => null,
        ]);

        Personnel::query()->create([
            'fname' => 'Janet',
            'lname' => 'Duplicate',
            'position' => 'Senior Technician',
            'employee_id' => 'EMP-DUPE-1',
            'email' => 'jane.duplicate@example.test',
        ]);

        $this->getJson(route('api.inventory.personnels.index.guest', ['search' => 'EMP-DUPE-1']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.position', 'Senior Technician')
            ->assertJsonPath('data.0.email', 'jane.duplicate@example.test')
            ->assertJsonPath('data.0.has_email', true);
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

    public function test_guest_can_initialize_fresh_personnel_profile_by_employee_id(): void
    {
        $personnel = Personnel::query()->create([
            'fname' => 'Fresh',
            'lname' => 'Staff',
            'position' => 'Technician',
            'employee_id' => 'EMP-2002',
        ]);
        $personnel->forceFill(['updated_at' => null])->saveQuietly();

        $this->postJson(route('api.inventory.personnels.initialize-profile.guest'), [
            'employee_id' => 'EMP-2002',
            'fname' => 'Fresh',
            'mname' => 'M',
            'lname' => 'Staff',
            'suffix' => null,
            'position' => 'Senior Technician',
            'phone' => '09171234567',
            'address' => 'Science City of Munoz',
            'email' => 'fresh.staff@example.test',
        ])
            ->assertOk()
            ->assertJsonPath('data.profile_requires_update', false)
            ->assertJsonPath('data.fullName', 'Fresh M Staff')
            ->assertJsonPath('data.has_email', true);

        $this->assertDatabaseHas('personnels', [
            'employee_id' => 'EMP-2002',
            'position' => 'Senior Technician',
            'phone' => '09171234567',
        ]);
    }

    public function test_guest_can_update_missing_personnel_email_by_employee_id(): void
    {
        Personnel::query()->create([
            'fname' => 'Email',
            'lname' => 'Missing',
            'position' => 'Technician',
            'employee_id' => 'EMP-EMAIL-1',
            'email' => null,
        ]);

        $this->putJson(route('api.inventory.personnels.email.guest'), [
            'employee_id' => 'EMP-EMAIL-1',
            'email' => 'email.missing@example.test',
        ])
            ->assertOk()
            ->assertJsonPath('data.has_email', true)
            ->assertJsonPath('data.email', 'email.missing@example.test');

        $this->assertDatabaseHas('personnels', [
            'employee_id' => 'EMP-EMAIL-1',
            'email' => 'email.missing@example.test',
        ]);
    }
}
