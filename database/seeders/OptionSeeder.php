<?php

namespace Database\Seeders;

use App\Models\Option;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $options = [
            // System Configuration
            [
                'key' => 'app_name',
                'value' => 'CBC Apps',
                'label' => 'Application Name',
                'description' => 'The name of the application',
                'type' => 'text',
                'group' => 'system',
            ],
            [
                'key' => 'app_description',
                'value' => 'Centralized Booking and Cataloging System',
                'label' => 'Application Description',
                'description' => 'Short description of the application',
                'type' => 'textarea',
                'group' => 'system',
            ],
            [
                'key' => 'app_version',
                'value' => '1.0.0',
                'label' => 'Application Version',
                'description' => 'Current version of the application',
                'type' => 'text',
                'group' => 'system',
            ],
            [
                'key' => 'approving_officers',
                'value' => 'CRISTO REY C. MAGDADARO',
                'label' => 'Approving Officers',
                'description' => 'Name of approving officers',
                'type' => 'text',
                'group' => 'system',
            ],
            [
                'key' => 'center_chief',
                'value' => 'ROEL R. SURALTA',
                'label' => 'Center Chief',
                'description' => 'Name of the center chief',
                'type' => 'text',
                'group' => 'system',
            ],
            
            // Email Configuration
            [
                'key' => 'email_from_address',
                'value' => 'noreply@cbcapps.local',
                'label' => 'Email From Address',
                'description' => 'Default email address for sending notifications',
                'type' => 'text',
                'group' => 'email',
            ],
            [
                'key' => 'email_from_name',
                'value' => 'CBC Apps',
                'label' => 'Email From Name',
                'description' => 'Default name for sender in emails',
                'type' => 'text',
                'group' => 'email',
            ],
            [
                'key' => 'outgoing_transaction_notification_email',
                'value' => '',
                'label' => 'Outgoing Transaction Notification Email',
                'description' => 'Email for outgoing transaction notifications',
                'type' => 'text',
                'group' => 'email',
            ],
            [
                'key' => 'event_response_notification_email',
                'value' => '',
                'label' => 'Event Response Notification Email',
                'description' => 'Email for event response notifications',
                'type' => 'text',
                'group' => 'email',
            ],
            
            // Inventory Settings
            [
                'key' => 'low_stock_threshold',
                'value' => '25',
                'label' => 'Low Stock Threshold (%)',
                'description' => 'Percentage below which items are considered low stock',
                'type' => 'number',
                'group' => 'inventory',
            ],
            [
                'key' => 'enable_inventory_notifications',
                'value' => 'true',
                'label' => 'Enable Inventory Notifications',
                'description' => 'Send notifications for low stock and transactions',
                'type' => 'boolean',
                'group' => 'inventory',
            ],
            
            // Form Settings
            [
                'key' => 'form_submission_deadline_days',
                'value' => '7',
                'label' => 'Form Submission Deadline (Days)',
                'description' => 'Number of days before form expiration',
                'type' => 'number',
                'group' => 'forms',
            ],
            [
                'key' => 'max_form_slots',
                'value' => '100',
                'label' => 'Maximum Form Slots',
                'description' => 'Maximum number of participants per form',
                'type' => 'number',
                'group' => 'forms',
            ],
            
            // Rental Settings
            [
                'key' => 'rental_cancellation_days',
                'value' => '3',
                'label' => 'Rental Cancellation Notice (Days)',
                'description' => 'Number of days notice required for cancellation',
                'type' => 'number',
                'group' => 'rental',
            ],

            // Inventory Enums
            [
                'key' => 'transaction_types',
                'value' => 'incoming',
                'label' => 'Transaction Types',
                'description' => 'Available transaction types',
                'type' => 'json',
                'group' => 'inventory',
                'options' => json_encode(['incoming', 'outgoing']),
            ],
            [
                'key' => 'stock_levels',
                'value' => json_encode([
                    ['name' => 'empty', 'label' => 'Empty Stock (0%)'],
                    ['name' => 'low', 'label' => 'Low Stock (25%)'],
                    ['name' => 'mid', 'label' => 'Mid Stock (75%)'],
                    ['name' => 'high', 'label' => 'High Stock (100%)'],
                ]),
                'label' => 'Stock Level Categories',
                'description' => 'Categorization of stock levels',
                'type' => 'json',
                'group' => 'inventory',
            ],

            // Request Types
            [
                'key' => 'request_type_supplies',
                'value' => json_encode(['Office Supplies', 'ICT Supplies', 'Laboratory Consumables']),
                'label' => 'Supplies Request Types',
                'description' => 'Types of supplies that can be requested',
                'type' => 'json',
                'group' => 'requests',
            ],
            [
                'key' => 'request_type_equipment',
                'value' => json_encode(['ICT Equipment', 'Laboratory Equipment', 'Plant Growth Chamber', 'Biofreezer', 'Medicool']),
                'label' => 'Equipment Request Types',
                'description' => 'Types of equipment that can be requested',
                'type' => 'json',
                'group' => 'requests',
            ],
            [
                'key' => 'request_type_materials',
                'value' => json_encode(['IEC Materials', 'Tokens']),
                'label' => 'Materials Request Types',
                'description' => 'Types of materials that can be requested',
                'type' => 'json',
                'group' => 'requests',
            ],
            [
                'key' => 'request_type_spaces',
                'value' => json_encode(['Office Space', 'Laboratory Access', 'Screenhouse Space', 'Storage Space', 'Utility Space', 'Field Experimental Space', 'Parking Space']),
                'label' => 'Spaces Request Types',
                'description' => 'Types of spaces that can be requested',
                'type' => 'json',
                'group' => 'requests',
            ],

            // Offices
            [
                'key' => 'offices',
                'value' => json_encode(['Researchers Office I', 'Researchers Office II', 'Accountant\'s Office', 'Prayer Room']),
                'label' => 'Office Locations',
                'description' => 'Available office locations',
                'type' => 'json',
                'group' => 'locations',
            ],

            // Screenhouses
            [
                'key' => 'screenhouses',
                'value' => json_encode(['Screenhouse 1', 'Screenhouse 2', 'Screenhouse 3']),
                'label' => 'Screenhouse Locations',
                'description' => 'Available screenhouses',
                'type' => 'json',
                'group' => 'locations',
            ],

            // Laboratories
            [
                'key' => 'laboratories',
                'value' => json_encode([
                    ['name' => 'bioinforoom', 'label' => 'Bioinformatics Room'],
                    ['name' => 'moleculargeneticsroom', 'label' => 'Molecular Genetics Room'],
                    ['name' => 'genetictransroom', 'label' => 'Genome Engineering Laboratory'],
                    ['name' => 'tissuecultureroom', 'label' => 'Tissue Culture Room'],
                    ['name' => 'systembiologyroom', 'label' => 'Systems Biology Room'],
                    ['name' => 'microbialbiotechroom', 'label' => 'Microbial Biotechnology Room'],
                    ['name' => 'moleculardiagnosticsroom', 'label' => 'Molecular Diagnostics Room'],
                    ['name' => 'diagnosticlab', 'label' => 'Diagnostic Laboratory'],
                    ['name' => 'phenotypingarea', 'label' => 'Phenotyping Area'],
                    ['name' => 'microscopesequenceroom', 'label' => 'Microscope and Sequencing Room'],
                    ['name' => 'generalequipmentarea', 'label' => 'General Equipment Area'],
                    ['name' => 'sampleprocessingroom', 'label' => 'Sample Processing Room'],
                    ['name' => 'washroomi', 'label' => 'Wash Room I'],
                ]),
                'label' => 'Laboratory Locations',
                'description' => 'Available laboratory rooms',
                'type' => 'json',
                'group' => 'locations',
            ],

            // Vehicles
            [
                'key' => 'vehicles',
                'value' => json_encode([
                    ['name' => 'innova', 'label' => 'Innova'],
                    ['name' => 'pickup', 'label' => 'Pickup Truck'],
                    ['name' => 'van', 'label' => 'Van'],
                    ['name' => 'suv', 'label' => 'SUV'],
                    ['name' => 'coaster', 'label' => 'Coaster'],
                    ['name' => 'ebike', 'label' => 'E-Bike'],
                    ['name' => 'bike', 'label' => 'Bike'],
                    ['name' => 'tractor', 'label' => 'Tractor'],
                ]),
                'label' => 'Available Vehicles',
                'description' => 'Vehicles available for rental',
                'type' => 'json',
                'group' => 'rental',
            ],

            // Event Halls
            [
                'key' => 'event_halls',
                'value' => json_encode([
                    ['name' => 'plenary', 'label' => 'Plenary Hall'],
                    ['name' => 'trainingroom', 'label' => 'Training Room'],
                    ['name' => 'mph', 'label' => 'Multi-Purpose Hall'],
                ]),
                'label' => 'Event Halls',
                'description' => 'Available event halls for rental',
                'type' => 'json',
                'group' => 'rental',
            ],

            // Storage Locations
            [
                'key' => 'storage_locations',
                'value' => json_encode([
                    ['name' => 'roi', 'label' => 'Researchers Office I'],
                    ['name' => 'roii', 'label' => 'Researchers Office II'],
                    ['name' => 'bioinforoom', 'label' => 'Bioinformatics Room'],
                    ['name' => 'moleculargeneticsroom', 'label' => 'Molecular Genetics Room'],
                    ['name' => 'genetictransroom', 'label' => 'Genome Engineering Laboratory'],
                    ['name' => 'tissuecultureroom', 'label' => 'Tissue Culture Room'],
                    ['name' => 'systembiologyroom', 'label' => 'Systems Biology Room'],
                    ['name' => 'microbialbiotechroom', 'label' => 'Microbial Biotechnology Room'],
                    ['name' => 'moleculardiagnosticsroom', 'label' => 'Molecular Diagnostics Room'],
                    ['name' => 'diagnosticlab', 'label' => 'Diagnostic Laboratory'],
                    ['name' => 'phenotypingarea', 'label' => 'Phenotyping Area'],
                    ['name' => 'microscopesequenceroom', 'label' => 'Microscope and Sequencing Room'],
                    ['name' => 'generalequipmentarea', 'label' => 'General Equipment Area'],
                    ['name' => 'sampleprocessingroom', 'label' => 'Sample Processing Room'],
                    ['name' => 'washroomi', 'label' => 'Wash Room I'],
                    ['name' => 'washroomii', 'label' => 'Wash Room II'],
                    ['name' => 'centralbodega', 'label' => 'Central Bodega'],
                    ['name' => 'bodegaone', 'label' => 'Bodega 1'],
                    ['name' => 'bodegatwo', 'label' => 'Bodega 2'],
                    ['name' => 'plenary', 'label' => 'Plenary Hall'],
                    ['name' => 'trainingroom', 'label' => 'Training Room'],
                    ['name' => 'mph', 'label' => 'Multi-Purpose Hall'],
                    ['name' => 'motorpool', 'label' => 'Motor Pool'],
                    ['name' => 'aadelacruz', 'label' => 'AADelaCruz Office'],
                    ['name' => 'rrsuralta', 'label' => 'RRSuralta Office'],
                    ['name' => 'aaoffice', 'label' => 'AA Office'],
                    ['name' => 'meetingroom', 'label' => 'Meeting Room'],
                    ['name' => 'suppliesroom1', 'label' => 'Supplies Room I'],
                    ['name' => 'suppliesroom2', 'label' => 'Supplies Room II'],
                    ['name' => 'consultantoffice', 'label' => 'Consultant Office'],
                    ['name' => 'darkroom', 'label' => 'Dark Room'],
                    ['name' => 'dnaextractionroom', 'label' => 'DNA Extraction Room'],
                    ['name' => 'freezerroom', 'label' => 'Freezer Room'],
                    ['name' => 'lightroom', 'label' => 'Light Room'],
                    ['name' => 'screenhouse1', 'label' => 'Screenhouse 1'],
                ]),
                'label' => 'Storage Locations',
                'description' => 'All available storage and inventory locations',
                'type' => 'json',
                'group' => 'inventory',
            ],

            // Report Templates - Incident Report
            [
                'key' => 'report_template_incident',
                'value' => json_encode([
                    'label' => 'Incident / Damage Report',
                    'description' => 'Document laboratory incidents, damaged consumables, or malfunctioning equipment associated with a transaction.',
                    'fields' => [
                        'incident_date' => ['label' => 'Incident Date', 'type' => 'date', 'placeholder' => 'YYYY-MM-DD', 'rules' => 'required|date'],
                        'location' => ['label' => 'Location / Laboratory', 'type' => 'text', 'placeholder' => 'e.g. Molecular Genetics Laboratory', 'rules' => 'required|string|max:255'],
                        'reported_by' => ['label' => 'Reported By', 'type' => 'text', 'placeholder' => 'Name of personnel', 'rules' => 'required|string|max:255'],
                        'impact_summary' => ['label' => 'Impact / Damage Summary', 'type' => 'textarea', 'placeholder' => 'Describe what happened and which assets were affected.', 'rules' => 'required|string'],
                        'immediate_action' => ['label' => 'Immediate Action Taken', 'type' => 'textarea', 'placeholder' => 'Repairs, isolation, escalation, etc.', 'rules' => 'nullable|string'],
                        'status' => ['label' => 'Operational Status', 'type' => 'select', 'options' => [['name' => 'operational', 'label' => 'Operational'], ['name' => 'needs_repair', 'label' => 'Needs Repair'], ['name' => 'decommissioned', 'label' => 'Decommissioned']], 'rules' => 'required|string|in:operational,needs_repair,decommissioned'],
                    ],
                ]),
                'label' => 'Incident Report Template',
                'description' => 'Template for incident and damage reports',
                'type' => 'json',
                'group' => 'reports',
            ],

            // Report Templates - Maintenance Report
            [
                'key' => 'report_template_maintenance',
                'value' => json_encode([
                    'label' => 'Preventive Maintenance Report',
                    'description' => 'Attach calibration or preventive maintenance details for ICT and laboratory equipment.',
                    'fields' => [
                        'inspection_date' => ['label' => 'Inspection Date', 'type' => 'date', 'rules' => 'required|date'],
                        'technician' => ['label' => 'Technician / Service Provider', 'type' => 'text', 'rules' => 'required|string|max:255'],
                        'inspection_notes' => ['label' => 'Inspection Notes', 'type' => 'textarea', 'rules' => 'required|string'],
                        'next_due_date' => ['label' => 'Next Due Date', 'type' => 'date', 'rules' => 'nullable|date|after_or_equal:inspection_date'],
                        'status' => ['label' => 'Status After Maintenance', 'type' => 'select', 'options' => [['name' => 'pass', 'label' => 'Passed QC'], ['name' => 'monitor', 'label' => 'Monitor / Observation'], ['name' => 'fail', 'label' => 'Failed QC']], 'rules' => 'required|string|in:pass,monitor,fail'],
                    ],
                ]),
                'label' => 'Maintenance Report Template',
                'description' => 'Template for preventive maintenance reports',
                'type' => 'json',
                'group' => 'reports',
            ],

            // Report Templates - Utilization Report
            [
                'key' => 'report_template_utilization',
                'value' => json_encode([
                    'label' => 'Utilization / Release Report',
                    'description' => 'Summarize how consumables or equipment were issued, returned, or consumed for a project.',
                    'fields' => [
                        'released_to' => ['label' => 'Released To / Requester', 'type' => 'text', 'rules' => 'required|string|max:255'],
                        'project_reference' => ['label' => 'Project / Reference Code', 'type' => 'text', 'rules' => 'nullable|string|max:255'],
                        'usage_window' => ['label' => 'Usage Window', 'type' => 'text', 'placeholder' => 'e.g. 2024-05-01 to 2024-05-05', 'rules' => 'nullable|string|max:255'],
                        'return_condition' => ['label' => 'Return Condition / Remarks', 'type' => 'textarea', 'rules' => 'required|string'],
                        'completeness_check' => ['label' => 'Completeness Check', 'type' => 'select', 'options' => [['name' => 'complete', 'label' => 'Complete'], ['name' => 'partial', 'label' => 'Partially Returned'], ['name' => 'missing', 'label' => 'Missing Items']], 'rules' => 'required|string|in:complete,partial,missing'],
                    ],
                ]),
                'label' => 'Utilization Report Template',
                'description' => 'Template for utilization and release reports',
                'type' => 'json',
                'group' => 'reports',
            ],
        ];

        foreach ($options as $option) {
            Option::factory()->create($option);
        }
    }
}
