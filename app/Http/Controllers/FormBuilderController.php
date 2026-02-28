<?php

namespace App\Http\Controllers;

use App\Repositories\FormBuilderRepo;
use App\Models\FormTypeTemplate;
use App\Models\FormFieldDefinition;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class FormBuilderController extends Controller
{
    protected FormBuilderRepo $repository;

    public function __construct(FormBuilderRepo $repository)
    {
        $this->repository = $repository;
    }

    /**
     * List all templates (system + user's custom)
     */
    public function indexTemplates(Request $request): JsonResponse
    {
        $templates = $this->repository->getTemplatesWithFieldCounts();

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
     * Create a new custom template
     */
    public function storeTemplate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'fields' => 'required|array|min:1',
            'fields.*.field_key' => 'required|string|max:100',
            'fields.*.field_type' => 'required|string|in:' . implode(',', array_keys(FormFieldDefinition::FIELD_TYPES)),
            'fields.*.label' => 'required|string|max:255',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.description' => 'nullable|string|max:500',
            'fields.*.validation_rules' => 'nullable|array',
            'fields.*.options' => 'nullable|array',
            'fields.*.display_config' => 'nullable|array',
            'fields.*.field_config' => 'nullable|array',
        ]);

        $template = $this->repository->createTemplateWithFields(
            [
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'icon' => $validated['icon'] ?? null,
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

        // Prevent editing system templates (they can be duplicated instead)
        if ($template->is_system && !$request->user()?->hasRole('admin')) {
            return response()->json([
                'message' => 'System templates cannot be edited. Duplicate it to create a custom version.',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'icon' => 'nullable|string|max:50',
            'fields' => 'sometimes|array|min:1',
            'fields.*.id' => 'nullable|uuid',
            'fields.*.field_key' => 'required|string|max:100',
            'fields.*.field_type' => 'required|string|in:' . implode(',', array_keys(FormFieldDefinition::FIELD_TYPES)),
            'fields.*.label' => 'required|string|max:255',
            'fields.*.placeholder' => 'nullable|string|max:255',
            'fields.*.description' => 'nullable|string|max:500',
            'fields.*.validation_rules' => 'nullable|array',
            'fields.*.options' => 'nullable|array',
            'fields.*.display_config' => 'nullable|array',
            'fields.*.field_config' => 'nullable|array',
        ]);

        $template = $this->repository->updateTemplateWithFields(
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

        $newTemplate = $this->repository->duplicateTemplate($template, Auth::id());

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
        $template = $this->repository->findBySlug($slug);

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
}
