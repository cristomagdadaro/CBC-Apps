<?php

namespace Tests\Unit\Services;

use App\Services\DynamicValidationService;
use Tests\TestCase;

class DynamicValidationServiceTest extends TestCase
{
    public function test_it_builds_required_email_rules_from_schema(): void
    {
        $rules = app(DynamicValidationService::class)->buildRulesFromSchema([
            [
                'field_key' => 'email',
                'field_type' => 'email',
                'validation_rules' => ['required' => true],
            ],
        ]);

        $this->assertSame(['required', 'email'], $rules['email']);
    }

    public function test_checkbox_agreement_uses_accepted_rule_when_required(): void
    {
        $rules = app(DynamicValidationService::class)->buildFieldRules([
            'field_type' => 'checkbox_agreement',
            'validation_rules' => ['required' => true],
        ]);

        $this->assertSame(['accepted'], array_values($rules));
    }

    public function test_it_builds_messages_attributes_and_defaults(): void
    {
        $schema = [
            [
                'field_key' => 'full_name',
                'label' => 'Full Name',
                'field_type' => 'text',
                'validation_rules' => [
                    'required' => true,
                    'required_message' => 'Full name is required.',
                ],
                'field_config' => [],
            ],
            [
                'field_key' => 'preferences',
                'label' => 'Preferences',
                'field_type' => 'checkboxes',
                'options' => [
                    ['value' => 'email'],
                    ['value' => 'sms'],
                ],
            ],
        ];

        $service = app(DynamicValidationService::class);
        $messages = $service->buildMessagesFromSchema($schema);
        $attributes = $service->buildAttributesFromSchema($schema);
        $defaults = $service->getDefaultValues($schema);

        $this->assertSame('Full name is required.', $messages['full_name.required']);
        $this->assertSame('Full Name', $attributes['full_name']);
        $this->assertNull($defaults['full_name']);
        $this->assertSame([], $defaults['preferences']);
    }
}
