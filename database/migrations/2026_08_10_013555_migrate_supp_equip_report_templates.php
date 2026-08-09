<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $templates = [
            'incident_report' => [
                'label' => 'Incident / Damage Report',
                'description' => 'Document laboratory incidents, damaged consumables, or malfunctioning equipment associated with a transaction.',
                'fields' => [
                    'incident_date' => [
                        'label' => 'Incident Date',
                        'type' => 'date',
                        'placeholder' => 'YYYY-MM-DD',
                        'rules' => 'required|date',
                    ],
                    'location' => [
                        'label' => 'Location / Laboratory',
                        'type' => 'text',
                        'placeholder' => 'e.g. Molecular Genetics Laboratory',
                        'rules' => 'required|string|max:255',
                    ],
                    'reported_by' => [
                        'label' => 'Reported By',
                        'type' => 'text',
                        'placeholder' => 'Name of personnel',
                        'rules' => 'required|string|max:255',
                    ],
                    'impact_summary' => [
                        'label' => 'Impact / Damage Summary',
                        'type' => 'textarea',
                        'placeholder' => 'Describe what happened and which assets were affected.',
                        'rules' => 'required|string',
                    ],
                    'immediate_action' => [
                        'label' => 'Immediate Action Taken',
                        'type' => 'textarea',
                        'placeholder' => 'Repairs, isolation, escalation, etc.',
                        'rules' => 'nullable|string',
                    ],
                    'status' => [
                        'label' => 'Operational Status',
                        'type' => 'select',
                        'options' => [
                            ['name' => 'operational', 'label' => 'Operational'],
                            ['name' => 'needs_repair', 'label' => 'Needs Repair'],
                            ['name' => 'decommissioned', 'label' => 'Decommissioned'],
                        ],
                        'rules' => 'required|string|in:operational,needs_repair,decommissioned',
                    ],
                ],
            ],
            'maintenance_report' => [
                'label' => 'Preventive Maintenance Report',
                'description' => 'Attach calibration or preventive maintenance details for ICT and laboratory equipment.',
                'fields' => [
                    'inspection_date' => [
                        'label' => 'Inspection Date',
                        'type' => 'date',
                        'rules' => 'required|date',
                    ],
                    'technician' => [
                        'label' => 'Technician / Service Provider',
                        'type' => 'text',
                        'rules' => 'required|string|max:255',
                    ],
                    'inspection_notes' => [
                        'label' => 'Inspection Notes',
                        'type' => 'textarea',
                        'rules' => 'required|string',
                    ],
                    'next_due_date' => [
                        'label' => 'Next Due Date',
                        'type' => 'date',
                        'rules' => 'nullable|date|after_or_equal:inspection_date',
                    ],
                    'status' => [
                        'label' => 'Status After Maintenance',
                        'type' => 'select',
                        'options' => [
                            ['name' => 'pass', 'label' => 'Passed QC'],
                            ['name' => 'monitor', 'label' => 'Monitor / Observation'],
                            ['name' => 'fail', 'label' => 'Failed QC'],
                        ],
                        'rules' => 'required|string|in:pass,monitor,fail',
                    ],
                ],
            ],
            'utilization_report' => [
                'label' => 'Utilization / Release Report',
                'description' => 'Summarize how consumables or equipment were issued, returned, or consumed for a project.',
                'fields' => [
                    'released_to' => [
                        'label' => 'Released To / Requester',
                        'type' => 'text',
                        'rules' => 'required|string|max:255',
                    ],
                    'project_reference' => [
                        'label' => 'Project / Reference Code',
                        'type' => 'text',
                        'rules' => 'nullable|string|max:255',
                    ],
                    'usage_window' => [
                        'label' => 'Usage Window',
                        'type' => 'text',
                        'placeholder' => 'e.g. 2024-05-01 to 2024-05-05',
                        'rules' => 'nullable|string|max:255',
                    ],
                    'return_condition' => [
                        'label' => 'Return Condition / Remarks',
                        'type' => 'textarea',
                        'rules' => 'required|string',
                    ],
                    'completeness_check' => [
                        'label' => 'Completeness Check',
                        'type' => 'select',
                        'options' => [
                            ['name' => 'complete', 'label' => 'Complete'],
                            ['name' => 'partial', 'label' => 'Partially Returned'],
                            ['name' => 'missing', 'label' => 'Missing Items'],
                        ],
                        'rules' => 'required|string|in:complete,partial,missing',
                    ],
                ],
            ],
        ];

        $now = Carbon::now();

        foreach ($templates as $key => $template) {
            DB::table('options')->updateOrInsert(
                ['key' => $key, 'group' => 'supp_equip_report_templates'],
                [
                    'id' => Str::uuid()->toString(),
                    'label' => $template['label'],
                    'description' => $template['description'],
                    'type' => 'json',
                    'options' => json_encode(['fields' => $template['fields']]),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('options')->where('group', 'supp_equip_report_templates')->delete();
    }
};
