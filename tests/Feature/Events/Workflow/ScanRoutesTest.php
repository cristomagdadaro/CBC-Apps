<?php

namespace Tests\Feature\Events\Workflow;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\WithTestRoles;

class ScanRoutesTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->createAdminUser();
    }

    public function test_scan_requires_authentication(): void
    {
        $response = $this->postJson('/api/forms/event/0504/scan', [
            'payload' => (string) Str::uuid(),
            'scan_type' => 'checkin',
        ]);

        $response->assertUnauthorized();
    }

    /**
     * @dataProvider invalidPayloadProvider
     */
    public function test_scan_validates_payload_format(string $case, mixed $payload): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/forms/event/0504/scan', [
            'payload' => $payload,
            'scan_type' => 'checkin',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['payload']);
    }

    public static function invalidPayloadProvider(): array
    {
        return [
            'empty_string' => ['empty', ''],
            'whitespace_only' => ['whitespace', '   '],
            'too_short' => ['short', 'abc'],
            'array_instead_of_string' => ['array', ['nested' => 'value']],
            'null_value' => ['null', null],
        ];
    }

    /**
     * @dataProvider invalidScanTypeProvider
     */
    public function test_scan_validates_scan_type(string $case, string $scanType): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/forms/event/0504/scan', [
            'payload' => (string) Str::uuid(),
            'scan_type' => $scanType,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['scan_type']);
    }

    public static function invalidScanTypeProvider(): array
    {
        return [
            'empty' => ['empty', ''],
            'wrong_case' => ['case', 'CHECKIN'],
            'hyphenated' => ['hyphen', 'check-in'],
            'similar_but_invalid' => ['similar', 'snack'],
            'sql_injection_attempt' => ['sql', "'; DROP TABLE scans;--"],
            'xss_attempt' => ['xss', '<script>alert(1)</script>'],
            'nonexistent_type' => ['random', 'nonexistent_type'],
        ];
    }

    public function test_scan_accepts_all_valid_scan_types(): void
    {
        $validTypes = ['checkin', 'certificate', 'meal', 'breakfast', 'lunch', 
                       'dinner', 'snack_am', 'snack_pm', 'quiz', 'workshop'];

        foreach ($validTypes as $type) {
            $response = $this->actingAs($this->admin)->postJson('/api/forms/event/0504/scan', [
                'payload' => (string) Str::uuid(),
                'scan_type' => $type,
                'terminal_id' => 'terminal-a',
            ]);

            $response->assertOk()
                ->assertJsonStructure(['status', 'message']);
        }
    }

    public function test_scan_accepts_optional_metadata(): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/forms/event/0504/scan', [
            'payload' => (string) Str::uuid(),
            'scan_type' => 'checkin',
            'terminal_id' => 'terminal-a',
            'metadata' => ['location' => 'hall-a', 'operator' => 'staff-001'],
        ]);

        $response->assertOk();
    }
}