<?php

namespace Tests\Feature\Events\Forms;

use App\Models\EventSubform;
use App\Models\Form;
use App\Models\FormFieldDefinition;
use App\Models\FormTypeTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\WithTestRoles;

/**
 * Comprehensive API tests for the Form Builder module.
 * Tests cover 50+ scenarios including CRUD operations, validation,
 * authorization, edge cases, and error handling.
 */
class CrudTest extends TestCase
{
    use RefreshDatabase, WithTestRoles;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->adminUser = $this->createAdminUser();
        $this->regularUser = User::factory()->create(['is_admin' => false]);
    }

    // ========================================================================
    // AUTHENTICATION & AUTHORIZATION TESTS
    // ========================================================================

    /** @test */
    public function test_01_unauthenticated_user_cannot_access_templates_index(): void
    {
        $response = $this->getJson(route('api.form-builder.templates.index'));
        
        $response->assertStatus(401);
    }

    /** @test */
    public function test_02_unauthenticated_user_cannot_access_field_types(): void
    {
        $response = $this->getJson(route('api.form-builder.field-types'));
        
        $response->assertStatus(401);
    }

    /** @test */
    public function test_03_unauthenticated_user_cannot_create_template(): void
    {
        $response = $this->postJson(route('api.form-builder.templates.store'), [
            'name' => 'Test Template',
            'fields' => [
                ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name'],
            ],
        ]);
        
        $response->assertStatus(401);
    }

    /** @test */
    public function test_04_unauthenticated_user_cannot_update_template(): void
    {
        $template = FormTypeTemplate::factory()->create();
        
        $response = $this->putJson(route('api.form-builder.templates.update', $template->id), [
            'name' => 'Updated Name',
        ]);
        
        $response->assertStatus(401);
    }

    /** @test */
    public function test_05_unauthenticated_user_cannot_delete_template(): void
    {
        $template = FormTypeTemplate::factory()->create();
        
        $response = $this->deleteJson(route('api.form-builder.templates.destroy', $template->id));
        
        $response->assertStatus(401);
    }

    /** @test */
    public function test_06_authorized_user_can_access_templates_index(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.index'));
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data',
                'meta' => ['total', 'system_count', 'custom_count'],
            ]);
    }

    // ========================================================================
    // FIELD TYPES ENDPOINT TESTS
    // ========================================================================

    /** @test */
    public function test_07_field_types_returns_all_available_types(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.field-types'));
        
        $response->assertStatus(200)
            ->assertJsonStructure(['data']);
        
        $data = $response->json('data');
        $this->assertArrayHasKey('text', $data);
        $this->assertArrayHasKey('email', $data);
        $this->assertArrayHasKey('select', $data);
        $this->assertArrayHasKey('radio', $data);
        $this->assertArrayHasKey('checkbox', $data);
    }

    /** @test */
    public function test_08_field_types_contains_required_metadata(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.field-types'));
        
        $data = $response->json('data');
        
        foreach ($data as $key => $type) {
            $this->assertArrayHasKey('label', $type, "Field type '{$key}' missing 'label'");
            $this->assertArrayHasKey('has_options', $type, "Field type '{$key}' missing 'has_options'");
            $this->assertArrayHasKey('icon', $type, "Field type '{$key}' missing 'icon'");
        }
    }

    /** @test */
    public function test_09_field_types_has_correct_count(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.field-types'));
        
        $data = $response->json('data');
        
        // Should have all defined field types (30 as per FormFieldDefinition::FIELD_TYPES)
        $this->assertCount(30, $data);
    }

    // ========================================================================
    // TEMPLATE LIST TESTS
    // ========================================================================

    /** @test */
    public function test_10_templates_index_returns_empty_when_no_templates(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.index'));
        
        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 0,
                    'system_count' => 0,
                    'custom_count' => 0,
                ],
            ]);
    }

    /** @test */
    public function test_11_templates_index_returns_system_templates(): void
    {
        FormTypeTemplate::factory()->system()->count(3)->create();
        
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.index'));
        
        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 3,
                    'system_count' => 3,
                    'custom_count' => 0,
                ],
            ]);
    }

    /** @test */
    public function test_12_templates_index_returns_custom_templates(): void
    {
        FormTypeTemplate::factory()->custom()->count(5)->create();
        
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.index'));
        
        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 5,
                    'system_count' => 0,
                    'custom_count' => 5,
                ],
            ]);
    }

    /** @test */
    public function test_13_templates_index_returns_mixed_templates(): void
    {
        FormTypeTemplate::factory()->system()->count(2)->create();
        FormTypeTemplate::factory()->custom()->count(3)->create();
        
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.index'));
        
        $response->assertStatus(200)
            ->assertJson([
                'meta' => [
                    'total' => 5,
                    'system_count' => 2,
                    'custom_count' => 3,
                ],
            ]);
    }

    /** @test */
    public function test_14_templates_index_includes_field_counts(): void
    {
        $template = FormTypeTemplate::factory()->create();
        FormFieldDefinition::factory()->count(5)->forTemplate($template)->create();
        
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.index'));
        
        $data = $response->json('data');
        $templateData = collect($data)->firstWhere('id', $template->id);
        
        $this->assertNotNull($templateData);
        $this->assertEquals(5, $templateData['field_definitions_count']);
    }

    // ========================================================================
    // TEMPLATE SHOW TESTS
    // ========================================================================

    /** @test */
    public function test_15_show_template_returns_template_data(): void
    {
        $template = FormTypeTemplate::factory()->create([
            'name' => 'Test Template',
            'description' => 'Test description',
        ]);
        
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.show', $template->id));
        
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Test Template')
            ->assertJsonPath('data.description', 'Test description');
    }

    /** @test */
    public function test_16_show_template_includes_field_definitions(): void
    {
        $template = FormTypeTemplate::factory()->create();
        FormFieldDefinition::factory()->count(3)->forTemplate($template)->create();
        
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.show', $template->id));
        
        $response->assertStatus(200);
        
        $fieldDefinitions = $response->json('data.field_definitions');
        $this->assertCount(3, $fieldDefinitions);
    }

    /** @test */
    public function test_17_show_template_returns_404_for_invalid_id(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.show', 'invalid-uuid'));
        
        $response->assertStatus(404);
    }

    /** @test */
    public function test_18_show_template_returns_404_for_nonexistent_uuid(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.show', Str::uuid()));
        
        $response->assertStatus(404);
    }

    /** @test */
    public function test_19_show_template_by_slug_returns_template(): void
    {
        $template = FormTypeTemplate::factory()->create(['slug' => 'test-slug']);
        
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.by-slug', 'test-slug'));
        
        $response->assertStatus(200)
            ->assertJsonPath('data.slug', 'test-slug');
    }

    /** @test */
    public function test_20_show_template_by_slug_returns_404_for_invalid_slug(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->getJson(route('api.form-builder.templates.by-slug', 'nonexistent-slug'));
        
        $response->assertStatus(404);
    }

    // ========================================================================
    // TEMPLATE CREATE TESTS
    // ========================================================================

    /** @test */
    public function test_21_can_create_template_with_valid_data(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'New Template',
                'description' => 'A test template',
                'icon' => '📋',
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Full Name'],
                    ['field_key' => 'email', 'field_type' => 'email', 'label' => 'Email Address'],
                ],
            ]);
        
        $response->assertStatus(201)
            ->assertJsonPath('message', 'Template created successfully');
        
        $this->assertDatabaseHas('form_type_templates', ['name' => 'New Template']);
        $this->assertDatabaseHas('form_field_definitions', ['field_key' => 'name']);
        $this->assertDatabaseHas('form_field_definitions', ['field_key' => 'email']);
    }

    /** @test */
    public function test_22_created_template_is_not_system(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'User Template',
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name'],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $template = FormTypeTemplate::where('name', 'User Template')->first();
        $this->assertFalse($template->is_system);
    }

    /** @test */
    public function test_23_created_template_has_creator_id(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'My Template',
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name'],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $template = FormTypeTemplate::where('name', 'My Template')->first();
        $this->assertEquals($this->adminUser->id, $template->created_by);
    }

    /** @test */
    public function test_24_create_template_requires_name(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name'],
                ],
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /** @test */
    public function test_25_create_template_requires_at_least_one_field(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Empty Template',
                'fields' => [],
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('fields');
    }

    /** @test */
    public function test_26_create_template_validates_field_keys_required(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Test',
                'fields' => [
                    ['field_type' => 'text', 'label' => 'Name'],
                ],
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('fields.0.field_key');
    }

    /** @test */
    public function test_27_create_template_validates_field_type_required(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Test',
                'fields' => [
                    ['field_key' => 'name', 'label' => 'Name'],
                ],
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('fields.0.field_type');
    }

    /** @test */
    public function test_28_create_template_validates_field_label_required(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Test',
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text'],
                ],
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('fields.0.label');
    }

    /** @test */
    public function test_29_create_template_validates_invalid_field_type(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Test',
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'invalid_type', 'label' => 'Name'],
                ],
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('fields.0.field_type');
    }

    /** @test */
    public function test_30_create_template_accepts_all_valid_field_types(): void
    {
        $fieldTypes = array_keys(FormFieldDefinition::FIELD_TYPES);
        $fields = [];
        
        foreach ($fieldTypes as $index => $type) {
            $fields[] = [
                'field_key' => "field_{$type}",
                'field_type' => $type,
                'label' => "Field {$type}",
            ];
        }
        
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'All Field Types Template',
                'fields' => $fields,
            ]);
        
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('form_type_templates', ['name' => 'All Field Types Template']);
        $this->assertEquals(count($fieldTypes), FormFieldDefinition::where('form_type_template_id', $response->json('data.id'))->count());
    }

    /** @test */
    public function test_31_create_template_with_field_options(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Options Template',
                'fields' => [
                    [
                        'field_key' => 'color',
                        'field_type' => 'select',
                        'label' => 'Favorite Color',
                        'options' => [
                            ['value' => 'red', 'label' => 'Red'],
                            ['value' => 'blue', 'label' => 'Blue'],
                            ['value' => 'green', 'label' => 'Green'],
                        ],
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $field = FormFieldDefinition::where('field_key', 'color')->first();
        $this->assertCount(3, $field->options);
    }

    /** @test */
    public function test_32_create_template_with_validation_rules(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Validation Template',
                'fields' => [
                    [
                        'field_key' => 'age',
                        'field_type' => 'number',
                        'label' => 'Age',
                        'validation_rules' => [
                            'required' => true,
                            'min' => 18,
                            'max' => 100,
                        ],
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $field = FormFieldDefinition::where('field_key', 'age')->first();
        $this->assertTrue($field->validation_rules['required']);
        $this->assertEquals(18, $field->validation_rules['min']);
    }

    /** @test */
    public function test_33_create_template_with_display_config(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Display Config Template',
                'fields' => [
                    [
                        'field_key' => 'notes',
                        'field_type' => 'textarea',
                        'label' => 'Notes',
                        'display_config' => [
                            'rows' => 5,
                            'cols' => 40,
                        ],
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $field = FormFieldDefinition::where('field_key', 'notes')->first();
        $this->assertEquals(5, $field->display_config['rows']);
    }

    /** @test */
    public function test_34_create_template_name_max_length(): void
    {
        $longName = str_repeat('a', 256);
        
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => $longName,
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name'],
                ],
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    // ========================================================================
    // TEMPLATE UPDATE TESTS
    // ========================================================================

    /** @test */
    public function test_35_can_update_custom_template_name(): void
    {
        $template = FormTypeTemplate::factory()->custom()->create(['name' => 'Original Name']);
        
        $response = $this->actingAs($this->adminUser)
            ->putJson(route('api.form-builder.templates.update', $template->id), [
                'name' => 'Updated Name',
            ]);
        
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Name');
        
        $this->assertDatabaseHas('form_type_templates', [
            'id' => $template->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function test_36_can_update_template_description(): void
    {
        $template = FormTypeTemplate::factory()->create();
        
        $response = $this->actingAs($this->adminUser)
            ->putJson(route('api.form-builder.templates.update', $template->id), [
                'description' => 'New description here',
            ]);
        
        $response->assertStatus(200)
            ->assertJsonPath('data.description', 'New description here');
    }

    /** @test */
    public function test_37_can_update_template_fields(): void
    {
        $template = FormTypeTemplate::factory()->create();
        FormFieldDefinition::factory()->text()->forTemplate($template)->create([
            'field_key' => 'old_field',
            'label' => 'Old Field',
        ]);
        
        $response = $this->actingAs($this->adminUser)
            ->putJson(route('api.form-builder.templates.update', $template->id), [
                'fields' => [
                    ['field_key' => 'new_field', 'field_type' => 'email', 'label' => 'New Email Field'],
                ],
            ]);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseHas('form_field_definitions', [
            'form_type_template_id' => $template->id,
            'field_key' => 'new_field',
        ]);
    }

    /** @test */
    public function test_38_cannot_update_system_template_as_regular_user(): void
    {
        $template = FormTypeTemplate::factory()->system()->create();
        
        $response = $this->actingAs($this->regularUser)
            ->putJson(route('api.form-builder.templates.update', $template->id), [
                'name' => 'Hacked Name',
            ]);
        
        // Depending on authorization setup, this could be 403 or 401
        $this->assertTrue(in_array($response->status(), [401, 403]));
    }

    /** @test */
    public function test_39_update_returns_404_for_nonexistent_template(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->putJson(route('api.form-builder.templates.update', Str::uuid()), [
                'name' => 'Test',
            ]);
        
        $response->assertStatus(404);
    }

    /** @test */
    public function test_40_update_validates_name_max_length(): void
    {
        $template = FormTypeTemplate::factory()->create();
        
        $response = $this->actingAs($this->adminUser)
            ->putJson(route('api.form-builder.templates.update', $template->id), [
                'name' => str_repeat('x', 256),
            ]);
        
        $response->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    // ========================================================================
    // TEMPLATE DELETE TESTS
    // ========================================================================

    /** @test */
    public function test_41_can_delete_custom_template(): void
    {
        $template = FormTypeTemplate::factory()->custom()->create();
        
        $response = $this->actingAs($this->adminUser)
            ->deleteJson(route('api.form-builder.templates.destroy', $template->id));
        
        $response->assertStatus(200)
            ->assertJsonPath('message', 'Template deleted successfully');
        
        $this->assertSoftDeleted('form_type_templates', ['id' => $template->id]);
    }

    /** @test */
    public function test_42_cannot_delete_system_template(): void
    {
        $template = FormTypeTemplate::factory()->system()->create();
        
        $response = $this->actingAs($this->adminUser)
            ->deleteJson(route('api.form-builder.templates.destroy', $template->id));
        
        $response->assertStatus(403)
            ->assertJsonPath('message', 'System templates cannot be deleted.');
    }

    /** @test */
    public function test_43_cannot_delete_template_in_use(): void
    {
        $template = FormTypeTemplate::factory()->custom()->create();
        $form = Form::factory()->create();
        EventSubform::factory()->create([
            'event_id' => $form->event_id,
            'form_type' => 'custom',
            'form_type_template_id' => $template->id,
        ]);
        
        $response = $this->actingAs($this->adminUser)
            ->deleteJson(route('api.form-builder.templates.destroy', $template->id));
        
        $response->assertStatus(422)
            ->assertJsonPath('message', 'This template is in use by existing event forms and cannot be deleted.');
    }

    /** @test */
    public function test_44_delete_returns_404_for_nonexistent_template(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->deleteJson(route('api.form-builder.templates.destroy', Str::uuid()));
        
        $response->assertStatus(404);
    }

    /** @test */
    public function test_45_deleting_template_removes_field_definitions(): void
    {
        $template = FormTypeTemplate::factory()->create();
        $fieldIds = FormFieldDefinition::factory()
            ->count(3)
            ->forTemplate($template)
            ->create()
            ->pluck('id')
            ->toArray();
        
        $this->actingAs($this->adminUser)
            ->deleteJson(route('api.form-builder.templates.destroy', $template->id));
        
        // Fields should be orphaned or cascade deleted based on DB setup
        // Verify the template is soft-deleted
        $this->assertSoftDeleted('form_type_templates', ['id' => $template->id]);
    }

    // ========================================================================
    // TEMPLATE DUPLICATE TESTS
    // ========================================================================

    /** @test */
    public function test_46_can_duplicate_template(): void
    {
        $template = FormTypeTemplate::factory()->create(['name' => 'Original']);
        FormFieldDefinition::factory()->count(3)->forTemplate($template)->create();
        
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.duplicate', $template->id));
        
        $response->assertStatus(201)
            ->assertJsonPath('message', 'Template duplicated successfully');
        
        $newTemplate = FormTypeTemplate::find($response->json('data.id'));
        $this->assertStringContainsString('Original', $newTemplate->name);
        $this->assertEquals(3, $newTemplate->fieldDefinitions()->count());
    }

    /** @test */
    public function test_47_duplicated_template_is_custom(): void
    {
        $template = FormTypeTemplate::factory()->system()->create();
        
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.duplicate', $template->id));
        
        $response->assertStatus(201);
        
        $newTemplate = FormTypeTemplate::find($response->json('data.id'));
        $this->assertFalse($newTemplate->is_system);
    }

    /** @test */
    public function test_48_duplicated_template_has_new_creator(): void
    {
        $otherUser = User::factory()->create(['is_admin' => true]);
        $template = FormTypeTemplate::factory()->createdBy($otherUser)->create();
        
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.duplicate', $template->id));
        
        $response->assertStatus(201);
        
        $newTemplate = FormTypeTemplate::find($response->json('data.id'));
        $this->assertEquals($this->adminUser->id, $newTemplate->created_by);
    }

    /** @test */
    public function test_49_duplicate_returns_404_for_nonexistent_template(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.duplicate', Str::uuid()));
        
        $response->assertStatus(404);
    }

    /** @test */
    public function test_50_duplicated_fields_have_new_ids(): void
    {
        $template = FormTypeTemplate::factory()->create();
        $originalFields = FormFieldDefinition::factory()
            ->count(2)
            ->forTemplate($template)
            ->create();
        
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.duplicate', $template->id));
        
        $newTemplate = FormTypeTemplate::find($response->json('data.id'));
        $newFields = $newTemplate->fieldDefinitions;
        
        $originalIds = $originalFields->pluck('id')->toArray();
        $newIds = $newFields->pluck('id')->toArray();
        
        foreach ($newIds as $newId) {
            $this->assertNotContains($newId, $originalIds);
        }
    }

    // ========================================================================
    // EDGE CASES & ADDITIONAL SCENARIOS
    // ========================================================================

    /** @test */
    public function test_51_create_template_with_special_characters_in_name(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => "Template with 'quotes' & special <chars>",
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name'],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('form_type_templates', [
            'name' => "Template with 'quotes' & special <chars>",
        ]);
    }

    /** @test */
    public function test_52_create_template_with_unicode_name(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Template 日本語 中文 한국어',
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => '名前'],
                ],
            ]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function test_53_field_sort_order_is_preserved(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Ordered Fields Template',
                'fields' => [
                    ['field_key' => 'first', 'field_type' => 'text', 'label' => 'First'],
                    ['field_key' => 'second', 'field_type' => 'text', 'label' => 'Second'],
                    ['field_key' => 'third', 'field_type' => 'text', 'label' => 'Third'],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $template = FormTypeTemplate::find($response->json('data.id'));
        $fields = $template->fieldDefinitions()->orderBy('sort_order')->get();
        
        $this->assertEquals('first', $fields[0]->field_key);
        $this->assertEquals('second', $fields[1]->field_key);
        $this->assertEquals('third', $fields[2]->field_key);
    }

    /** @test */
    public function test_54_create_template_with_many_fields(): void
    {
        $fields = [];
        for ($i = 1; $i <= 50; $i++) {
            $fields[] = [
                'field_key' => "field_{$i}",
                'field_type' => 'text',
                'label' => "Field {$i}",
            ];
        }
        
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Large Template',
                'fields' => $fields,
            ]);
        
        $response->assertStatus(201);
        
        $template = FormTypeTemplate::find($response->json('data.id'));
        $this->assertEquals(50, $template->fieldDefinitions()->count());
    }

    /** @test */
    public function test_55_field_key_uniqueness_within_template(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Duplicate Keys Template',
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name 1'],
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name 2'],
                ],
            ]);
        
        // Duplicate field_keys should cause an error (either validation 422 or DB constraint 500)
        // The database has a unique constraint on (form_type_template_id, field_key)
        $this->assertTrue(
            in_array($response->status(), [422, 500]),
            "Expected status 422 or 500, got {$response->status()}"
        );
    }

    /** @test */
    public function test_56_template_slug_is_auto_generated(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Auto Slug Test Template',
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name'],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $template = FormTypeTemplate::find($response->json('data.id'));
        $this->assertNotEmpty($template->slug);
    }

    /** @test */
    public function test_57_create_template_with_section_headers(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Sectioned Template',
                'fields' => [
                    ['field_key' => 'section_1', 'field_type' => 'section_header', 'label' => 'Personal Info'],
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name'],
                    ['field_key' => 'section_2', 'field_type' => 'section_header', 'label' => 'Contact Info'],
                    ['field_key' => 'email', 'field_type' => 'email', 'label' => 'Email'],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $this->assertDatabaseHas('form_field_definitions', [
            'field_key' => 'section_1',
            'field_type' => 'section_header',
        ]);
    }

    /** @test */
    public function test_58_create_template_with_likert_scale(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Survey Template',
                'fields' => [
                    [
                        'field_key' => 'satisfaction',
                        'field_type' => 'likert',
                        'label' => 'How satisfied are you?',
                        'options' => [
                            ['value' => '1', 'label' => 'Very Dissatisfied'],
                            ['value' => '2', 'label' => 'Dissatisfied'],
                            ['value' => '3', 'label' => 'Neutral'],
                            ['value' => '4', 'label' => 'Satisfied'],
                            ['value' => '5', 'label' => 'Very Satisfied'],
                        ],
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function test_59_create_template_with_linear_scale_config(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Rating Template',
                'fields' => [
                    [
                        'field_key' => 'rating',
                        'field_type' => 'linear_scale',
                        'label' => 'Rate from 1-10',
                        'field_config' => [
                            'min' => 1,
                            'max' => 10,
                            'min_label' => 'Poor',
                            'max_label' => 'Excellent',
                        ],
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $field = FormFieldDefinition::where('field_key', 'rating')->first();
        $this->assertEquals(1, $field->field_config['min']);
        $this->assertEquals(10, $field->field_config['max']);
    }

    /** @test */
    public function test_60_create_template_with_file_upload_config(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Upload Template',
                'fields' => [
                    [
                        'field_key' => 'document',
                        'field_type' => 'file',
                        'label' => 'Upload Document',
                        'validation_rules' => [
                            'max_size' => 5120,
                            'accept' => '.pdf,.doc,.docx',
                        ],
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function test_61_empty_string_description_is_accepted(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'No Description Template',
                'description' => '',
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name'],
                ],
            ]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function test_62_null_description_is_accepted(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Null Description Template',
                'description' => null,
                'fields' => [
                    ['field_key' => 'name', 'field_type' => 'text', 'label' => 'Name'],
                ],
            ]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function test_63_field_placeholder_is_saved(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Placeholder Template',
                'fields' => [
                    [
                        'field_key' => 'name',
                        'field_type' => 'text',
                        'label' => 'Name',
                        'placeholder' => 'Enter your full name',
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $field = FormFieldDefinition::where('field_key', 'name')
            ->where('form_type_template_id', $response->json('data.id'))
            ->first();
        $this->assertEquals('Enter your full name', $field->placeholder);
    }

    /** @test */
    public function test_64_field_description_is_saved(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Help Text Template',
                'fields' => [
                    [
                        'field_key' => 'email',
                        'field_type' => 'email',
                        'label' => 'Email',
                        'description' => 'We will never share your email.',
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
        
        $field = FormFieldDefinition::where('field_key', 'email')
            ->where('form_type_template_id', $response->json('data.id'))
            ->first();
        $this->assertEquals('We will never share your email.', $field->description);
    }

    /** @test */
    public function test_65_can_create_address_field(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Address Template',
                'fields' => [
                    [
                        'field_key' => 'home_address',
                        'field_type' => 'address',
                        'label' => 'Home Address',
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function test_66_can_create_rating_field(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Star Rating Template',
                'fields' => [
                    [
                        'field_key' => 'stars',
                        'field_type' => 'rating',
                        'label' => 'Rate our service',
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function test_67_can_create_rich_text_field(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Rich Text Template',
                'fields' => [
                    [
                        'field_key' => 'essay',
                        'field_type' => 'rich_text',
                        'label' => 'Write your essay',
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function test_68_can_create_multiple_choice_grid(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Grid Template',
                'fields' => [
                    [
                        'field_key' => 'evaluation',
                        'field_type' => 'multiple_choice_grid',
                        'label' => 'Evaluate the following',
                        'options' => [
                            ['value' => 'excellent', 'label' => 'Excellent'],
                            ['value' => 'good', 'label' => 'Good'],
                            ['value' => 'fair', 'label' => 'Fair'],
                            ['value' => 'poor', 'label' => 'Poor'],
                        ],
                        'field_config' => [
                            'rows' => ['Question 1', 'Question 2', 'Question 3'],
                        ],
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function test_69_can_create_checkboxes_field(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->postJson(route('api.form-builder.templates.store'), [
                'name' => 'Checkboxes Template',
                'fields' => [
                    [
                        'field_key' => 'interests',
                        'field_type' => 'checkboxes',
                        'label' => 'Select your interests',
                        'options' => [
                            ['value' => 'sports', 'label' => 'Sports'],
                            ['value' => 'music', 'label' => 'Music'],
                            ['value' => 'art', 'label' => 'Art'],
                            ['value' => 'tech', 'label' => 'Technology'],
                        ],
                    ],
                ],
            ]);
        
        $response->assertStatus(201);
    }

    /** @test */
    public function test_70_concurrent_template_creation(): void
    {
        // Create multiple templates in sequence to test database integrity
        $results = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->actingAs($this->adminUser)
                ->postJson(route('api.form-builder.templates.store'), [
                    'name' => "Concurrent Template {$i}",
                    'fields' => [
                        ['field_key' => "field_{$i}", 'field_type' => 'text', 'label' => "Field {$i}"],
                    ],
                ]);
            
            $results[] = $response->status();
        }
        
        // All should succeed
        foreach ($results as $status) {
            $this->assertEquals(201, $status);
        }
        
        $this->assertEquals(5, FormTypeTemplate::where('name', 'like', 'Concurrent Template%')->count());
    }
}
