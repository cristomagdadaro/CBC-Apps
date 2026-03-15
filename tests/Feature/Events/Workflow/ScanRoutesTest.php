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

    public function test_scan_route_rejects_unauthenticated_access(): void
    {
        $response = $this->postJson('/api/forms/event/0504/scan', [
            'payload' => (string) Str::uuid(),
            'scan_type' => 'checkin',
        ]);

        $response->assertStatus(401);
    }

    /**
     * @dataProvider invalidScanPayloadProvider
     */
    public function test_scan_route_rejects_invalid_payloads(string $payload): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/forms/event/0504/scan', [
            'payload' => $payload,
            'scan_type' => 'checkin',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['payload']);
    }

    /**
     * @dataProvider invalidScanTypeProvider
     */
    public function test_scan_route_rejects_invalid_scan_types(string $scanType): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/forms/event/0504/scan', [
            'payload' => (string) Str::uuid(),
            'scan_type' => $scanType,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['scan_type']);
    }

    /**
     * @dataProvider validScanTypeProvider
     */
    public function test_scan_route_accepts_supported_scan_types(string $scanType): void
    {
        $response = $this->actingAs($this->admin)->postJson('/api/forms/event/0504/scan', [
            'payload' => (string) Str::uuid(),
            'scan_type' => $scanType,
            'terminal_id' => 'terminal-a',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message']);
    }

    public static function invalidScanPayloadProvider(): array
    {
        $cases = [];

        $base = ['', 'a', 'ab', 'abc', 'abcd', '    ', "\n\n\n", 'null'];
        foreach ($base as $index => $value) {
            $cases['base_payload_' . $index] = [$value];
        }

        for ($i = 0; $i < 32; $i++) {
            $cases['short_payload_' . $i] = [Str::repeat((string) (($i % 9) + 1), ($i % 4) + 1)];
        }

        return $cases;
    }

    public static function invalidScanTypeProvider(): array
    {
        $cases = [];

        $explicit = [
            '',
            'CHECKIN',
            'check-in',
            'snack',
            'snackam',
            'snack_pm_extra',
            'cert',
            'mealtime',
            'drop table',
            '123',
            'false',
            'null',
        ];

        foreach ($explicit as $index => $value) {
            $cases['explicit_invalid_type_' . $index] = [$value];
        }

        for ($i = 0; $i < 23; $i++) {
            $cases['generated_invalid_type_' . $i] = ['invalid_type_' . $i];
        }

        return $cases;
    }

    public static function validScanTypeProvider(): array
    {
        return [
            'checkin' => ['checkin'],
            'certificate' => ['certificate'],
            'meal' => ['meal'],
            'breakfast' => ['breakfast'],
            'lunch' => ['lunch'],
            'dinner' => ['dinner'],
            'snack_am' => ['snack_am'],
            'snack_pm' => ['snack_pm'],
            'quiz' => ['quiz'],
            'workshop' => ['workshop'],
        ];
    }
}
