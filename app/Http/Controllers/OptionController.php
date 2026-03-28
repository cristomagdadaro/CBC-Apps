<?php

namespace App\Http\Controllers;

use App\Http\Requests\Generic\GetRequest;
use App\Http\Requests\CreateOptionRequest;
use App\Http\Requests\UpdateOptionRequest;
use App\Http\Requests\DeleteOptionRequest;
use App\Repositories\OptionRepo;
use App\Repositories\CategoryRepo;
use App\Repositories\EventSubformRepo;
use App\Repositories\FormRepo;
use App\Repositories\ItemRepo;
use App\Repositories\LaboratoryEquipmentLogRepo;
use App\Repositories\ParticipantRepo;
use App\Repositories\PersonnelRepo;
use App\Repositories\SupplierRepo;
use App\Services\DeploymentAccessService;
use App\Services\EventWorkflowFeatureService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class OptionController extends BaseController
{
    protected array $workflowToggleMetadata = [
        EventWorkflowFeatureService::KEY_EVENT_WORKFLOW => [
            'label' => 'Event Workflow Enabled',
            'description' => 'Enable or disable event workflow sequence logic.',
            'group' => 'forms',
        ],
        EventWorkflowFeatureService::KEY_PARTICIPANT_WORKFLOW => [
            'label' => 'Participant Workflow Enabled',
            'description' => 'Enable or disable participant-dependent step progression logic.',
            'group' => 'forms',
        ],
        EventWorkflowFeatureService::KEY_PARTICIPANT_VERIFICATION => [
            'label' => 'Participant Verification Enabled',
            'description' => 'Enable or disable participant verification requirements in event forms.',
            'group' => 'forms',
        ],
    ];

    public function __construct(OptionRepo $repository)
    {
        $this->service = $repository;
    }

    protected function repo(): OptionRepo
    {
        return $this->service;
    }

    /**
     * List all options with optional filtering
     */
    public function index(GetRequest $request)
    {
        return $this->service->search(new Collection($request->validated()));
    }

    /**
     * Create a new option
     */
    public function store(CreateOptionRequest $request)
    {
        return parent::_store($request);
    }

    /**
     * Update an option
     */
    public function update(UpdateOptionRequest $request, string $id)
    {
        return parent::_update($id, $request);
    }

    /**
     * Delete an option
     */
    public function destroy(DeleteOptionRequest $request, string $id)
    {
        return parent::_destroy($id);
    }

    /**
     * Get options by group
     */
    public function getByGroup(Request $request, $group)
    {
        $options = $this->repo()->getByGroup($group);
        return new Collection($options);
    }

    /**
     * Get options by key
     */
    public function getByKey(Request $request, $key)
    {
        $value = $this->repo()->getByKey($key);
        
        // Try to decode if JSON, otherwise leave as string
        $decoded = @json_decode($value, true);
        if ($decoded !== null) {
            $value = $decoded;
        }
        
        return new Collection([
            'key' => $key,
            'value' => $value
        ]);
    }

    /**
     * Get all options grouped by group
     */
    public function getAllGrouped(Request $request)
    {
        $options = $this->repo()->getAllGrouped();
        return new Collection($options);
    }

    /**
     * Get options for dropdown with optional group filter
     */
    public function getForDropdown(Request $request)
    {
        $group = $request->query('group');
        $options = $this->repo()->getForDropdown($group);
        return new Collection($options);
    }

    /**
     * Get options with metadata
     */
    public function getWithMetadata(Request $request)
    {
        $group = $request->query('group');
        $options = $this->repo()->getWithMetadata($group);
        return new Collection($options);
    }

    /**
     * Get options for select fields by type
     * Unified endpoint for all database tables
     */
    public function getOptionsForSelect(Request $request, string $type)
    {
        $repositories = [
            'categories' => CategoryRepo::class,
            'event_subforms' => EventSubformRepo::class,
            'forms' => FormRepo::class,
            'items' => ItemRepo::class,
            'laboratory_equipment_logs' => LaboratoryEquipmentLogRepo::class,
            'participants' => ParticipantRepo::class,
            'personnels' => PersonnelRepo::class,
            'suppliers' => SupplierRepo::class,
        ];

        if (!isset($repositories[$type])) {
            return response()->json([
                'message' => "Invalid option type: {$type}",
                'available_types' => array_keys($repositories)
            ], 400);
        }

        $repositoryClass = $repositories[$type];
        $repository = app($repositoryClass);

        if (!method_exists($repository, 'getOptions')) {
            return response()->json([
                'message' => "Repository does not support getOptions method"
            ], 400);
        }

        $options = $repository->getOptions();
        return new Collection($options);
    }

    public function getWorkflowToggles(EventWorkflowFeatureService $workflowFeatures)
    {
        return response()->json([
            'status' => 'success',
            'data' => $workflowFeatures->toggles(),
            'keys' => [
                'event_workflow_enabled' => EventWorkflowFeatureService::KEY_EVENT_WORKFLOW,
                'participant_workflow_enabled' => EventWorkflowFeatureService::KEY_PARTICIPANT_WORKFLOW,
                'participant_verification_enabled' => EventWorkflowFeatureService::KEY_PARTICIPANT_VERIFICATION,
            ],
        ]);
    }

    public function updateWorkflowToggles(Request $request, EventWorkflowFeatureService $workflowFeatures)
    {
        $validated = $request->validate([
            'event_workflow_enabled' => ['required', 'boolean'],
            'participant_workflow_enabled' => ['required', 'boolean'],
            'participant_verification_enabled' => ['required', 'boolean'],
        ]);

        $mapping = [
            EventWorkflowFeatureService::KEY_EVENT_WORKFLOW => (bool) $validated['event_workflow_enabled'],
            EventWorkflowFeatureService::KEY_PARTICIPANT_WORKFLOW => (bool) $validated['participant_workflow_enabled'],
            EventWorkflowFeatureService::KEY_PARTICIPANT_VERIFICATION => (bool) $validated['participant_verification_enabled'],
        ];

        foreach ($mapping as $key => $value) {
            $this->repo()->upsertBooleanOption($key, $value, $this->workflowToggleMetadata[$key] ?? []);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Workflow toggles updated successfully.',
            'data' => $workflowFeatures->toggles(),
        ]);
    }

    public function getDeploymentAccess(DeploymentAccessService $deploymentAccess)
    {
        return response()->json([
            'status' => 'success',
            'data' => $deploymentAccess->managementPayload(),
        ]);
    }

    public function updateDeploymentAccess(Request $request, DeploymentAccessService $deploymentAccess)
    {
        $validated = $request->validate([
            'modules' => ['required', 'array', 'min:1'],
            'modules.*' => ['required', 'array'],
            'modules.*.access' => ['required', 'string', Rule::in(DeploymentAccessService::validAccessValues())],
            'modules.*.mode' => ['required', 'string', Rule::in(DeploymentAccessService::validModeValues())],
        ]);

        $allowedKeys = DeploymentAccessService::validModuleKeys();
        $invalidKeys = collect(array_keys($validated['modules']))
            ->diff($allowedKeys)
            ->values();

        if ($invalidKeys->isNotEmpty()) {
            return response()->json([
                'message' => 'Unknown module keys provided.',
                'invalid_keys' => $invalidKeys->all(),
            ], 422);
        }

        $lockedModules = collect($validated['modules'])
            ->filter(fn (array $settings, string $module) =>
                $settings['mode'] === DeploymentAccessService::MODE_DEACTIVATED
                && ! DeploymentAccessService::canBeDeactivated($module)
            )
            ->keys()
            ->values();

        if ($lockedModules->isNotEmpty()) {
            return response()->json([
                'message' => 'Protected modules cannot be deactivated from the UI.',
                'invalid_keys' => $lockedModules->all(),
            ], 422);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Module deployment settings updated successfully.',
            'data' => $deploymentAccess->updateModules($validated['modules']),
        ]);
    }
}
