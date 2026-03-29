<?php

namespace App\Http\Controllers;

use App\Repositories\FormBuilderRepo;
use App\Models\FormTypeTemplate;
use App\Models\FormFieldDefinition;
use App\Models\EventSubform;
use App\Services\DynamicValidationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class FormBuilderController extends BaseController
{
    public function __construct(FormBuilderRepo $repository)
    {
        $this->service = $repository;
    }

    /**
     * List all templates (system + user's custom)
     */
    public function indexTemplates(Request $request): JsonResponse
    {
        $templates = $this->formBuilderRepo()->getTemplatesWithFieldCounts();

        return response()->json([
            'data' => $templates,
            'meta' => [
                'total' => $templates->count(),
                'system_count' => $templates->where('is_system', true)->count(),
                'custom_count' => $templates->where('is_system', false)->count(),
            ],
        ]);
    }

    /**
     * Get templates for dropdown selection (lightweight)
     */
    public function templatesForSelect(Request $request): JsonResponse
    {
        $templates = FormTypeTemplate::query()
            ->select(['id', 'slug', 'name', 'description', 'icon', 'form_config', 'is_system'])
            ->withCount('fieldDefinitions')
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get()
            ->map(fn ($t) => [
                'value' => $t->id,
                'label' => $t->name,
                'slug' => $t->slug,
                'description' => $t->description,
                'icon' => $t->icon,
                'form_config' => is_array($t->form_config) ? $t->form_config : [],
                'is_system' => $t->is_system,
                'field_count' => $t->field_definitions_count,
            ]);

        return response()->json(['data' => $templates]);
    }

    /**
     * Get a single template with all field definitions
     */
    public function showTemplate(string $id): JsonResponse
    {
        $template = FormTypeTemplate::with('fieldDefinitions')->findOrFail($id);

        return response()->json([
            'data' => $template,
            'field_schema' => $template->field_schema,
        ]);
    }

    /**
     * Preview validation rules and messages for a template
     */
    public function previewValidation(Request $request, string $id): JsonResponse
    {
        $template = FormTypeTemplate::with('fieldDefinitions')->findOrFail($id);
        $dynamicService = app(DynamicValidationService::class);
        
        $schema = $template->field_schema;
        $rules = $dynamicService->buildRulesFromSchema($schema);
        $messages = $dynamicService->buildMessagesFromSchema($schema);

        return response()->json([
            'template_id' => $template->id,
            'template_name' => $template->name,
            'field_count' => count($schema),
            'validation_rules' => $rules,
            'validation_messages' => $messages,
            'schema' => $schema,
        ]);
    }

    /**
     * Assign a template to an event subform
     */
    public function assignToEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'event_subform_id' => 'required|uuid|exists:event_subforms,id',
            'template_id' => 'nullable|uuid|exists:form_type_templates,id',
            'copy_schema' => 'boolean',
        ]);

        $subform = EventSubform::findOrFail($validated['event_subform_id']);
        $templateId = $validated['template_id'];
        $copySchema = $validated['copy_schema'] ?? false;

        if ($copySchema && $templateId) {
            // Copy the template's schema to the subform (allows customization)
            $template = FormTypeTemplate::findOrFail($templateId);
            $subform->update([
                'form_type_template_id' => null,
                'field_schema' => $template->field_schema,
            ]);
        } else {
            // Link to template (schema resolved dynamically)
            $subform->update([
                'form_type_template_id' => $templateId,
                'field_schema' => null,
            ]);
        }

        return response()->json([
            'message' => $templateId 
                ? ($copySchema ? 'Template schema copied to event form' : 'Template assigned to event form')
                : 'Template removed from event form',
            'data' => $subform->fresh(['template']),
        ]);
    }

    /**
     * Get the resolved schema for an event subform
     */
    public function eventSubformSchema(string $subformId): JsonResponse
    {
        $subform = EventSubform::with('template')->findOrFail($subformId);

        return response()->json([
            'subform_id' => $subform->id,
            'event_id' => $subform->event_id,
            'form_type' => $subform->form_type,
            'template_id' => $subform->form_type_template_id,
            'has_custom_schema' => !empty($subform->field_schema),
            'schema' => $subform->resolved_field_schema,
            'template' => $subform->template ? [
                'id' => $subform->template->id,
                'name' => $subform->template->name,
                'slug' => $subform->template->slug,
            ] : null,
        ]);
    }

    /**
     * Create a new custom template
     */
    public function storeTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'form_config' => 'nullable|array',
            'fields' => 'required|array|min:1',
            'fields.*.field_key' => 'required|string|max:100|distinct',
            'fields.*.field_type' => 'required|string|in:' . implode(',', array_keys(FormFieldDefinition::FIELD_TYPES)),
            'fields.*.label' => 'required|string|max:1024',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.description' => 'nullable|string|max:500',
            'fields.*.validation_rules' => 'nullable|array',
            'fields.*.options' => 'nullable|array',
            'fields.*.display_config' => 'nullable|array',
            'fields.*.field_config' => 'nullable|array',
        ]);

        $template = $this->formBuilderRepo()->createTemplateWithFields(
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'icon' => $validated['icon'] ?? null,
                'form_config' => is_array($validated['form_config'] ?? null) ? $validated['form_config'] : [],
                'is_system' => false,
                'created_by' => Auth::id(),
            ],
            $validated['fields']
        );

        return response()->json([
            'message' => 'Template created successfully',
            'data' => $template,
        ], 201);
    }

    /**
     * Update an existing template
     */
    public function updateTemplate(Request $request, string $id): JsonResponse
    {
        $template = FormTypeTemplate::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'form_config' => 'nullable|array',
            'fields' => 'sometimes|array|min:1',
            'fields.*.id' => 'nullable|uuid',
            'fields.*.field_key' => 'required|string|max:100|distinct',
            'fields.*.field_type' => 'required|string|in:' . implode(',', array_keys(FormFieldDefinition::FIELD_TYPES)),
            'fields.*.label' => 'required|string|max:1024',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.description' => 'nullable|string|max:500',
            'fields.*.validation_rules' => 'nullable|array',
            'fields.*.options' => 'nullable|array',
            'fields.*.display_config' => 'nullable|array',
            'fields.*.field_config' => 'nullable|array',
        ]);

        $template = $this->formBuilderRepo()->updateTemplateWithFields(
            $template,
            $validated,
            $validated['fields'] ?? []
        );

        return response()->json([
            'message' => 'Template updated successfully',
            'data' => $template,
        ]);
    }

    /**
     * Delete a custom template
     */
    public function destroyTemplate(string $id): JsonResponse
    {
        $template = FormTypeTemplate::findOrFail($id);

        if ($template->is_system) {
            return response()->json([
                'message' => 'System templates cannot be deleted.',
            ], 403);
        }

        // Check if template is in use
        if ($template->eventSubforms()->exists()) {
            return response()->json([
                'message' => 'This template is in use by existing event forms and cannot be deleted.',
            ], 422);
        }

        $template->delete();

        return response()->json([
            'message' => 'Template deleted successfully',
        ]);
    }

    /**
     * Duplicate a template for customization
     */
    public function duplicateTemplate(string $id): JsonResponse
    {
        /** @var FormTypeTemplate $template */
        $template = FormTypeTemplate::with('fieldDefinitions')->findOrFail($id);

        $newTemplate = $this->formBuilderRepo()->duplicateTemplate($template, Auth::id());

        return response()->json([
            'message' => 'Template duplicated successfully',
            'data' => $newTemplate,
        ], 201);
    }

    /**
     * Get all available field types
     */
    public function fieldTypes(): JsonResponse
    {
        return response()->json([
            'data' => FormFieldDefinition::getFieldTypes(),
        ]);
    }

    /**
     * Get template by slug (for legacy form_type lookup)
     */
    public function showTemplateBySlug(string $slug): JsonResponse
    {
        $template = $this->formBuilderRepo()->findBySlug($slug);

        if (!$template) {
            return response()->json([
                'message' => 'Template not found',
            ], 404);
        }

        return response()->json([
            'data' => $template,
            'field_schema' => $template->field_schema,
        ]);
    }

    protected function formBuilderRepo(): FormBuilderRepo
    {
        return $this->requireService();
    }
}
