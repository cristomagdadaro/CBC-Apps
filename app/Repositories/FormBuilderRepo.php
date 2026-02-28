<?php

namespace App\Repositories;

use App\Models\FormTypeTemplate;
use App\Models\FormFieldDefinition;
use Illuminate\Support\Str;

class FormBuilderRepo extends AbstractRepoService
{
    public function __construct(FormTypeTemplate $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all templates with field counts
     */
    public function getTemplatesWithFieldCounts()
    {
        return $this->model
            ->withCount('fieldDefinitions')
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get system templates only
     */
    public function getSystemTemplates()
    {
        return $this->model
            ->system()
            ->with('fieldDefinitions')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get custom (user-created) templates
     */
    public function getCustomTemplates(?int $userId = null)
    {
        $query = $this->model->custom()->with('fieldDefinitions');
        
        if ($userId) {
            $query->where('created_by', $userId);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Create a new template with field definitions
     * @return FormTypeTemplate
     */
    public function createTemplateWithFields(array $data, array $fields): FormTypeTemplate
    {
        /** @var FormTypeTemplate $template */
        $template = $this->model->create([
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'icon' => $data['icon'] ?? null,
            'is_system' => $data['is_system'] ?? false,
            'created_by' => $data['created_by'] ?? null,
        ]);

        $this->syncFields($template, $fields);

        /** @var FormTypeTemplate */
        return $template->fresh(['fieldDefinitions']);
    }

    /**
     * Update template and its field definitions
     * @return FormTypeTemplate
     */
    public function updateTemplateWithFields(FormTypeTemplate $template, array $data, array $fields): FormTypeTemplate
    {
        $template->update([
            'name' => $data['name'] ?? $template->name,
            'description' => $data['description'] ?? $template->description,
            'icon' => $data['icon'] ?? $template->icon,
        ]);

        $this->syncFields($template, $fields);

        /** @var FormTypeTemplate */
        return $template->fresh(['fieldDefinitions']);
    }

    /**
     * Sync field definitions for a template
     */
    public function syncFields(FormTypeTemplate $template, array $fields): void
    {
        $existingIds = $template->fieldDefinitions()->pluck('id')->toArray();
        $incomingIds = [];

        foreach ($fields as $index => $fieldData) {
            $fieldId = $fieldData['id'] ?? null;

            $attributes = [
                'form_type_template_id' => $template->id,
                'field_key' => $fieldData['field_key'],
                'field_type' => $fieldData['field_type'],
                'label' => $fieldData['label'],
                'placeholder' => $fieldData['placeholder'] ?? null,
                'description' => $fieldData['description'] ?? null,
                'validation_rules' => $fieldData['validation_rules'] ?? [],
                'options' => $fieldData['options'] ?? [],
                'display_config' => $fieldData['display_config'] ?? [],
                'field_config' => $fieldData['field_config'] ?? [],
                'sort_order' => $index,
                'is_system' => $fieldData['is_system'] ?? false,
            ];

            if ($fieldId && in_array($fieldId, $existingIds)) {
                // Update existing field
                FormFieldDefinition::where('id', $fieldId)->update($attributes);
                $incomingIds[] = $fieldId;
            } else {
                // Create new field
                $newField = FormFieldDefinition::create($attributes);
                $incomingIds[] = $newField->id;
            }
        }

        // Delete fields that were removed
        $toDelete = array_diff($existingIds, $incomingIds);
        if (!empty($toDelete)) {
            FormFieldDefinition::whereIn('id', $toDelete)->delete();
        }
    }

    /**
     * Duplicate a template
     * @return FormTypeTemplate
     */
    public function duplicateTemplate(FormTypeTemplate $template, ?string $userId = null): FormTypeTemplate
    {
        /** @var FormTypeTemplate $newTemplate */
        $newTemplate = $this->model->create([
            'slug' => $template->slug . '-copy-' . Str::random(4),
            'name' => $template->name . ' (Copy)',
            'description' => $template->description,
            'icon' => $template->icon,
            'is_system' => false,
            'created_by' => $userId,
        ]);

        foreach ($template->fieldDefinitions as $field) {
            FormFieldDefinition::create([
                'form_type_template_id' => $newTemplate->id,
                'field_key' => $field->field_key,
                'field_type' => $field->field_type,
                'label' => $field->label,
                'placeholder' => $field->placeholder,
                'description' => $field->description,
                'validation_rules' => $field->validation_rules,
                'options' => $field->options,
                'display_config' => $field->display_config,
                'field_config' => $field->field_config,
                'sort_order' => $field->sort_order,
                'is_system' => false,
            ]);
        }

        /** @var FormTypeTemplate */
        return $newTemplate->fresh(['fieldDefinitions']);
    }

    /**
     * Get template by slug
     */
    public function findBySlug(string $slug): ?FormTypeTemplate
    {
        return $this->model->where('slug', $slug)->with('fieldDefinitions')->first();
    }

    /**
     * Get all available field types
     */
    public function getFieldTypes(): array
    {
        return FormFieldDefinition::getFieldTypes();
    }
}
