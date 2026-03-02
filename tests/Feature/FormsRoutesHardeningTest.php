<?php

namespace Tests\Feature;

use App\Models\EventSubform;
use App\Models\Form;
use App\Models\FormTypeTemplate;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\WithTestRoles;

class FormsRoutesHardeningTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
    }

    // ---------------------------------------------------------------------
    // forms.php route coverage
    // ---------------------------------------------------------------------

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

        // Scan may fail domain checks, but validation must pass
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message']);
    }

    /**
     * @dataProvider invalidTemplateStorePayloadProvider
     */
    public function test_template_store_validation_hardening(array $payload, string $expectedError): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('api.form-builder.templates.store'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([$expectedError]);
    }

    /**
     * @dataProvider invalidTemplateUpdatePayloadProvider
     */
    public function test_template_update_validation_hardening(array $patch, string $expectedError): void
    {
        $template = FormTypeTemplate::factory()->create();

        $payload = array_merge($this->validTemplatePayload(), $patch);

        $response = $this->actingAs($this->admin)
            ->putJson(route('api.form-builder.templates.update', $template->id), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([$expectedError]);
    }

    /**
     * @dataProvider invalidAssignTemplatePayloadProvider
     */
    public function test_assign_template_validation_hardening(array $payload, string $expectedError): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('api.form-builder.assign-template'), $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([$expectedError]);
    }

    // ---------------------------------------------------------------------
    // guest.php route coverage
    // ---------------------------------------------------------------------

    /**
     * @dataProvider invalidEmailProvider
     */
    public function test_guest_participant_lookup_validates_email(string $email): void
    {
        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode($email));

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_guest_participant_lookup_returns_not_found_for_unknown_email(): void
    {
        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode('missing@example.test'));

        $response->assertStatus(200)
            ->assertJsonPath('data.found', false)
            ->assertJsonPath('data.profile_found', false);
    }

    public function test_guest_participant_lookup_auto_creates_registration_if_profile_exists(): void
    {
        $participant = Participant::factory()->create([
            'email' => 'existing@example.test',
        ]);

        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode('existing@example.test'));

        $response->assertStatus(200)
            ->assertJsonPath('data.found', true)
            ->assertJsonPath('data.profile_found', true)
            ->assertJsonPath('data.participant.email', 'existing@example.test');

        $participantHash = $response->json('data.participant_hash');

        $this->assertNotEmpty($participantHash);
        $this->assertDatabaseHas('registrations', [
            'id' => $participantHash,
            'participant_id' => $participant->id,
            'event_subform_id' => '0504',
        ]);
    }

    public function test_guest_participant_lookup_returns_existing_registration_hash(): void
    {
        $participant = Participant::factory()->create([
            'email' => 'registered@example.test',
        ]);

        $registration = Registration::create([
            'id' => (string) Str::uuid(),
            'event_subform_id' => '0504',
            'participant_id' => $participant->id,
            'attendance_type' => null,
        ]);

        $response = $this->getJson('/api/guest/forms/event/0504/participant-lookup?email=' . urlencode('registered@example.test'));

        $response->assertStatus(200)
            ->assertJsonPath('data.found', true)
            ->assertJsonPath('data.participant_hash', $registration->id);
    }

    public function test_guest_workflow_state_returns_steps_and_form_config(): void
    {
        $form = Form::factory()->create(['event_id' => '0504']);

        $template = FormTypeTemplate::factory()->create([
            'form_config' => [
                'require_participant_verification' => false,
            ],
        ]);

        EventSubform::factory()->create([
            'event_id' => $form->event_id,
            'form_type' => 'custom_registration',
            'step_type' => 'registration',
            'is_enabled' => true,
            'is_required' => true,
            'form_type_template_id' => $template->id,
            'open_from' => now()->subHour()->toDateTimeString(),
            'open_to' => now()->addHour()->toDateTimeString(),
        ]);

        $response = $this->getJson('/api/guest/forms/event/0504/workflow');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.event_id', '0504')
            ->assertJsonPath('data.steps.0.form_config.require_participant_verification', false);
    }

    public function test_guest_workflow_state_for_unknown_event_returns_empty_steps(): void
    {
        $response = $this->getJson('/api/guest/forms/event/9999/workflow');

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.event_id', '9999')
            ->assertJsonCount(0, 'data.steps');
    }

    // ---------------------------------------------------------------------
    // Payload builders & providers
    // ---------------------------------------------------------------------

    protected function validTemplatePayload(): array
    {
        return [
            'name' => 'Hardening Template',
            'description' => 'Validation base payload',
            'icon' => '📋',
            'form_config' => [
                'require_participant_verification' => true,
            ],
            'fields' => [
                [
                    'field_key' => 'full_name',
                    'field_type' => 'text',
                    'label' => 'Full Name',
                    'validation_rules' => ['required' => true],
                    'options' => [],
                    'display_config' => [],
                    'field_config' => ['changeable' => true],
                ],
            ],
        ];
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

    public static function invalidTemplateStorePayloadProvider(): array
    {
        $base = [
            'name' => 'Template X',
            'description' => 'desc',
            'icon' => '📄',
            'fields' => [
                [
                    'field_key' => 'name',
                    'field_type' => 'text',
                    'label' => 'Name',
                ],
            ],
        ];

        return [
            'missing_name' => [array_diff_key($base, ['name' => true]), 'name'],
            'name_not_string' => [array_replace($base, ['name' => 123]), 'name'],
            'name_too_long' => [array_replace($base, ['name' => Str::repeat('a', 256)]), 'name'],
            'description_too_long' => [array_replace($base, ['description' => Str::repeat('a', 1001)]), 'description'],
            'icon_too_long' => [array_replace($base, ['icon' => Str::repeat('x', 51)]), 'icon'],
            'missing_fields' => [array_diff_key($base, ['fields' => true]), 'fields'],
            'fields_not_array' => [array_replace($base, ['fields' => 'invalid']), 'fields'],
            'fields_empty' => [array_replace($base, ['fields' => []]), 'fields'],
            'field_missing_key' => [array_replace($base, ['fields' => [['field_type' => 'text', 'label' => 'Name']]]), 'fields.0.field_key'],
            'field_missing_type' => [array_replace($base, ['fields' => [['field_key' => 'name', 'label' => 'Name']]]), 'fields.0.field_type'],
            'field_missing_label' => [array_replace($base, ['fields' => [['field_key' => 'name', 'field_type' => 'text']]]), 'fields.0.label'],
            'field_invalid_type' => [array_replace($base, ['fields' => [['field_key' => 'name', 'field_type' => 'unknown', 'label' => 'Name']]]), 'fields.0.field_type'],
            'field_key_too_long' => [array_replace($base, ['fields' => [['field_key' => Str::repeat('x', 101), 'field_type' => 'text', 'label' => 'Name']]]), 'fields.0.field_key'],
            'label_too_long' => [array_replace($base, ['fields' => [['field_key' => 'name', 'field_type' => 'text', 'label' => Str::repeat('x', 1025)]]]), 'fields.0.label'],
            'form_config_not_array' => [array_replace($base, ['form_config' => 'invalid']), 'form_config'],
        ];
    }

    public static function invalidTemplateUpdatePayloadProvider(): array
    {
        return [
            'name_not_string' => [['name' => 999], 'name'],
            'name_too_long' => [['name' => Str::repeat('n', 256)], 'name'],
            'description_too_long' => [['description' => Str::repeat('d', 1001)], 'description'],
            'icon_too_long' => [['icon' => Str::repeat('i', 51)], 'icon'],
            'fields_not_array' => [['fields' => 'invalid'], 'fields'],
            'fields_empty' => [['fields' => []], 'fields'],
            'field_id_not_uuid' => [['fields' => [['id' => 'not-uuid', 'field_key' => 'a', 'field_type' => 'text', 'label' => 'A']]], 'fields.0.id'],
            'field_type_invalid' => [['fields' => [['field_key' => 'a', 'field_type' => 'invalid', 'label' => 'A']]], 'fields.0.field_type'],
            'field_options_not_array' => [['fields' => [['field_key' => 'a', 'field_type' => 'text', 'label' => 'A', 'options' => 'bad']]], 'fields.0.options'],
            'form_config_not_array' => [['form_config' => 'bad'], 'form_config'],
        ];
    }

    public static function invalidAssignTemplatePayloadProvider(): array
    {
        $validSubform = (string) Str::uuid();
        $validTemplate = (string) Str::uuid();

        return [
            'missing_event_subform_id' => [['template_id' => $validTemplate], 'event_subform_id'],
            'event_subform_id_not_uuid' => [['event_subform_id' => '0504', 'template_id' => $validTemplate], 'event_subform_id'],
            'template_id_not_uuid' => [['event_subform_id' => $validSubform, 'template_id' => 'invalid'], 'template_id'],
            'copy_schema_not_bool' => [['event_subform_id' => $validSubform, 'template_id' => $validTemplate, 'copy_schema' => 'yes'], 'copy_schema'],
            'template_id_nonexistent' => [['event_subform_id' => $validSubform, 'template_id' => (string) Str::uuid()], 'template_id'],
            'event_subform_id_nonexistent' => [['event_subform_id' => (string) Str::uuid(), 'template_id' => null], 'event_subform_id'],
            'null_event_subform_id' => [['event_subform_id' => null, 'template_id' => null], 'event_subform_id'],
            'array_event_subform_id' => [['event_subform_id' => ['bad'], 'template_id' => null], 'event_subform_id'],
            'array_template_id' => [['event_subform_id' => $validSubform, 'template_id' => ['bad']], 'template_id'],
            'numeric_copy_schema' => [['event_subform_id' => $validSubform, 'template_id' => $validTemplate, 'copy_schema' => 3], 'copy_schema'],
        ];
    }

    public static function invalidEmailProvider(): array
    {
        $cases = [];

        $invalid = [
            '',
            'not-an-email',
            '@example.com',
            'user@',
            'user@@example.com',
            'user@ example.com',
            'user example.com',
            'userexample.com',
            'user@.com',
            'plainaddress',
        ];

        foreach ($invalid as $index => $value) {
            $cases['invalid_email_' . $index] = [$value];
        }

        return $cases;
    }
}
