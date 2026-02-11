<?php

namespace Database\Seeders;

use App\Models\Option;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Nette\Utils\Json;

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
            [
                'key' => 'sex',
                'value' => json_encode([
                    [ 'value' => 'Male', 'label' => 'Male', 'icon' => 'gender-male', 'color' => 'text-blue-300' ],
                    [ 'value' => 'Female', 'label' => 'Female', 'icon' => 'gender-female', 'color' => 'text-red-300' ],
                    [ 'value' => 'Prefer not to say', 'label' => 'Prefer not to say', 'icon' => 'balloon-heart', 'color' => 'text-yellow-300' ]
                ]),
                'label' => 'Sex',
                'description' => 'Available options for sex',
                'type' => 'json',
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
                'key' => 'event_response_notification_email',
                'value' => '',
                'label' => 'Event Response Notification Email',
                'description' => 'Email for event response notifications',
                'type' => 'text',
                'group' => 'email',
            ],
            
            // Inventory Settings
            [
                'key' => 'enable_inventory_notifications',
                'value' => 'true',
                'label' => 'Enable Inventory Notifications',
                'description' => 'Send notifications for low stock and transactions',
                'type' => 'boolean',
                'group' => 'inventory',
            ],
            [
                'key' => 'outgoing_transaction_notification_email',
                'value' => '',
                'label' => 'Outgoing Transaction Notification Email',
                'description' => 'Email for outgoing transaction notifications',
                'type' => 'text',
                'group' => 'inventory',
            ],

            // Inventory Enums
            [
                'key' => 'transaction_types',
                'value' => json_encode([['name' => 'incoming', 'label' => 'Incoming'], ['name' => 'outgoing', 'label' => 'Outgoing']]),
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
                'group' => 'fes',
            ],
            [
                'key' => 'request_type_equipment',
                'value' => json_encode(['ICT Equipment', 'Laboratory Equipment', 'Plant Growth Chamber', 'Biofreezer', 'Medicool']),
                'label' => 'Equipment Request Types',
                'description' => 'Types of equipment that can be requested',
                'type' => 'json',
                'group' => 'fes',
            ],
            [
                'key' => 'request_type_materials',
                'value' => json_encode(['IEC Materials', 'Tokens']),
                'label' => 'Materials Request Types',
                'description' => 'Types of materials that can be requested',
                'type' => 'json',
                'group' => 'fes',
            ],
            [
                'key' => 'request_type_spaces',
                'value' => json_encode(['Office Space', 'Laboratory Access', 'Screenhouse Space', 'Storage Space', 'Utility Space', 'Field Experimental Space', 'Parking Space']),
                'label' => 'Spaces Request Types',
                'description' => 'Types of spaces that can be requested',
                'type' => 'json',
                'group' => 'fes',
            ],

            // Offices
            [
                'key' => 'office_researchers_i',
                'value' => 'Researchers Office I',
                'label' => 'Researchers Office I',
                'description' => 'Office location for researchers',
                'type' => 'text',
                'group' => 'offices',
            ],
            [
                'key' => 'office_researchers_ii',
                'value' => 'Researchers Office II',
                'label' => 'Researchers Office II',
                'description' => 'Office location for researchers',
                'type' => 'text',
                'group' => 'offices',
            ],
            [
                'key' => 'office_accountant',
                'value' => 'Accountant\'s Office',
                'label' => 'Accountant\'s Office',
                'description' => 'Office location for accounting',
                'type' => 'text',
                'group' => 'offices',
            ],
            [
                'key' => 'office_prayer_room',
                'value' => 'Prayer Room',
                'label' => 'Prayer Room',
                'description' => 'Office location for prayer',
                'type' => 'text',
                'group' => 'offices',
            ],

            // Screenhouses
            [
                'key' => 'screenhouse_1',
                'value' => 'Screenhouse 1',
                'label' => 'Screenhouse 1',
                'description' => 'Screenhouse location for agricultural research',
                'type' => 'text',
                'group' => 'screenhouses',
            ],
            [
                'key' => 'screenhouse_2',
                'value' => 'Screenhouse 2',
                'label' => 'Screenhouse 2',
                'description' => 'Screenhouse location for agricultural research',
                'type' => 'text',
                'group' => 'screenhouses',
            ],
            [
                'key' => 'screenhouse_3',
                'value' => 'Screenhouse 3',
                'label' => 'Screenhouse 3',
                'description' => 'Screenhouse location for agricultural research',
                'type' => 'text',
                'group' => 'screenhouses',
            ],

            // Laboratories
            [
                'key' => 'lab_bioinformatics_room',
                'value' => 'Bioinformatics Room',
                'label' => 'Bioinformatics Room',
                'description' => 'Laboratory location for bioinformatics research',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_molecular_genetics_room',
                'value' => 'Molecular Genetics Room',
                'label' => 'Molecular Genetics Room',
                'description' => 'Laboratory location for molecular genetics research',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_genome_engineering',
                'value' => 'Genome Engineering Laboratory',
                'label' => 'Genome Engineering Laboratory',
                'description' => 'Laboratory location for genome engineering research',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_tissue_culture_room',
                'value' => 'Tissue Culture Room',
                'label' => 'Tissue Culture Room',
                'description' => 'Laboratory location for tissue culture research',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_systems_biology_room',
                'value' => 'Systems Biology Room',
                'label' => 'Systems Biology Room',
                'description' => 'Laboratory location for systems biology research',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_microbial_biotech_room',
                'value' => 'Microbial Biotechnology Room',
                'label' => 'Microbial Biotechnology Room',
                'description' => 'Laboratory location for microbial biotechnology research',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_molecular_diagnostics_room',
                'value' => 'Molecular Diagnostics Room',
                'label' => 'Molecular Diagnostics Room',
                'description' => 'Laboratory location for molecular diagnostics',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_diagnostic_lab',
                'value' => 'Diagnostic Laboratory',
                'label' => 'Diagnostic Laboratory',
                'description' => 'Laboratory location for diagnostic testing',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_phenotyping_area',
                'value' => 'Phenotyping Area',
                'label' => 'Phenotyping Area',
                'description' => 'Laboratory location for phenotyping research',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_microscope_sequencing_room',
                'value' => 'Microscope and Sequencing Room',
                'label' => 'Microscope and Sequencing Room',
                'description' => 'Laboratory location for microscopy and DNA sequencing',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_general_equipment_area',
                'value' => 'General Equipment Area',
                'label' => 'General Equipment Area',
                'description' => 'Laboratory location for general equipment storage and use',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_sample_processing_room',
                'value' => 'Sample Processing Room',
                'label' => 'Sample Processing Room',
                'description' => 'Laboratory location for sample processing',
                'type' => 'text',
                'group' => 'laboratories',
            ],
            [
                'key' => 'lab_wash_room_i',
                'value' => 'Wash Room I',
                'label' => 'Wash Room I',
                'description' => 'Laboratory location for equipment washing',
                'type' => 'text',
                'group' => 'laboratories',
            ],


            // Event Halls
            [
                'key' => 'event_hall_plenary',
                'value' => 'Plenary Hall',
                'label' => 'Plenary Hall',
                'description' => 'Event location for plenary sessions',
                'type' => 'text',
                'group' => 'event_halls',
            ],
            [
                'key' => 'event_hall_training_room',
                'value' => 'Training Room',
                'label' => 'Training Room',
                'description' => 'Event location for training sessions',
                'type' => 'text',
                'group' => 'event_halls',
            ],
            [
                'key' => 'event_hall_mph',
                'value' => 'Multi-Purpose Hall',
                'label' => 'Multi-Purpose Hall',
                'description' => 'Event location for multi-purpose activities',
                'type' => 'text',
                'group' => 'event_halls',
            ],

            // Storage Locations - Unique locations not in other groups
            [
                'key' => 'storage_loc_central_bodega',
                'value' => 'Central Bodega',
                'label' => 'Central Bodega',
                'description' => 'Central storage location for inventory',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_bodega_1',
                'value' => 'Bodega 1',
                'label' => 'Bodega 1',
                'description' => 'Primary bodega storage location',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_bodega_2',
                'value' => 'Bodega 2',
                'label' => 'Bodega 2',
                'description' => 'Secondary bodega storage location',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_motor_pool',
                'value' => 'Motor Pool',
                'label' => 'Motor Pool',
                'description' => 'Storage location at Motor Pool',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_aa_delacruz_office',
                'value' => 'AADelaCruz Office',
                'label' => 'AADelaCruz Office',
                'description' => 'Storage location in AADelaCruz Office',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_rr_suralta_office',
                'value' => 'RRSuralta Office',
                'label' => 'RRSuralta Office',
                'description' => 'Storage location in RRSuralta Office',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_aa_office',
                'value' => 'AA Office',
                'label' => 'AA Office',
                'description' => 'Storage location in AA Office',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_meeting_room',
                'value' => 'Meeting Room',
                'label' => 'Meeting Room',
                'description' => 'Storage location in Meeting Room',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_supplies_room_i',
                'value' => 'Supplies Room I',
                'label' => 'Supplies Room I',
                'description' => 'Primary supplies storage location',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_supplies_room_ii',
                'value' => 'Supplies Room II',
                'label' => 'Supplies Room II',
                'description' => 'Secondary supplies storage location',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_consultant_office',
                'value' => 'Consultant Office',
                'label' => 'Consultant Office',
                'description' => 'Storage location in Consultant Office',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_dark_room',
                'value' => 'Dark Room',
                'label' => 'Dark Room',
                'description' => 'Dark room storage location',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_dna_extraction_room',
                'value' => 'DNA Extraction Room',
                'label' => 'DNA Extraction Room',
                'description' => 'Storage location in DNA Extraction Room',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_freezer_room',
                'value' => 'Freezer Room',
                'label' => 'Freezer Room',
                'description' => 'Cold storage location for biological samples',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_light_room',
                'value' => 'Light Room',
                'label' => 'Light Room',
                'description' => 'Light room storage location',
                'type' => 'text',
                'group' => 'storage_locations',
            ],
            [
                'key' => 'storage_loc_wash_room_ii',
                'value' => 'Wash Room II',
                'label' => 'Wash Room II',
                'description' => 'Storage location in Wash Room II',
                'type' => 'text',
                'group' => 'storage_locations',
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
