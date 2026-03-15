<?php

namespace Tests\Feature\Events\Forms;

use App\Models\FormTypeTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\WithTestRoles;

class ValidationHardeningTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createAdminUser();
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
}
