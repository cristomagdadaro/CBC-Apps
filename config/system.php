<?php

use App\Enums\Inventory;

return [
    'approving_officers' => 'CRISTO REY C. MAGDADARO',
    'center_chief' => 'ROEL R. SURALTA',
    'outgoing_transaction_notification_email' => env('OUTGOING_TRANSACTION_NOTIFICATION_EMAIL'),
    'event_response_notification_email' => env('EVENT_RESPONSE_NOTIFICATION_EMAIL'),
    'transaction_type' => [
        Inventory::INCOMING->value,
        Inventory::OUTGOING->value,
    ],
    'stock_levels' => [
        [ 'name' => 'empty', 'label' => 'Empty Stock (0%)' ],
        [ 'name' => 'low', 'label' => 'Low Stock (25%)' ],
        [ 'name' => 'mid', 'label' => 'Mid Stock (75%)' ],
        [ 'name' => 'high', 'label' => 'High Stock (100%)' ],
    ],
    'request_types' => [
        'supplies' => [
            'Office Supplies',
            'ICT Supplies',
            'Laboratory Consumables',
        ],

        'equipment' => [
            'ICT Equipment',
            'Laboratory Equipment',
            'Plant Growth Chamber',
            'Biofreezer',
            'Medicool',
        ],

        'materials' => [
            'IEC Materials',
            'Tokens',
        ],

        'spaces' => [
            'Office Space',
            'Laboratory Access',
            'Screenhouse Space',
            'Storage Space',
            'Utility Space',
            'Field Experimental Space',
            'Parking Space',
        ],
    ],
    'offices' => [
        'Researchers Office I',
        'Researchers Office II',
        'Accountant\'s Office',
        'Prayer Room',
    ],
    'screenhouses' => [
        'Screenhouse 1',
        'Screenhouse 2',
        'Screenhouse 3',
    ],
    'laboratories' => [
        [
            'name' => Inventory::BIOINFOROOM->value,
            'label' =>  "Bioinformatics Room",
        ],
        [
            'name' => Inventory::MOLECULARGENETICSROOM->value,
            'label' => "Molecular Genetics Room",
        ],
        [
            'name' => Inventory::GENETICTRANSROOM->value,
            'label' => "Genome Enineering Laboratory",
        ],
        [
            'name' => Inventory::TISSUECULTUREROOM->value,
            'label' => "Tissue Culture Room",
        ],
        [
            'name' => Inventory::SYSTEMBIOLOGYROOM->value,
            'label' => "Systems Biology Room",
        ],
        [
            'name' => Inventory::MICROBIALBIOTECHROOM->value,
            'label' => "Microbial Biotechnology Room",
        ],
        [
            'name' => Inventory::MOLECULARDIAGNOSTICSROOM->value,
            'label' => "Molecular Diagnostics Room",
        ],
        [
            'name' => Inventory::DIAGNOSTICLAB->value,
            'label' => "Diagnostic Laboratory",
        ],
        [
            'name' => Inventory::PHENOTYPINGAREA->value,
            'label' => "Phenotyping Area",
        ],
        [
            'name' => Inventory::MICROSCOPESEQUENCEROOM->value,
            'label' => "Microscope and Sequencing Room",
        ],
        [
            'name' => Inventory::GENERALEQUIPMENTAREA->value,
            'label' => "General Equipment Area",
        ],
        [
            'name' => Inventory::SAMPLEPROCESSINGROOM->value,
            'label' => "Sample Processing Room",
        ],
        [
            'name' => Inventory::WASHROOMI->value,
            'label' => "Wash Room I",
        ],
        
    ],
    'vehicles' => [
        [
            'name' => Inventory::INNOVA->value,
            'label' => "Innova",
        ],
        [
            'name' => Inventory::PICKUP->value,
            'label' => "Pickup Truck",
        ],
        [
            'name' => Inventory::VAN->value,
            'label' => "Van",
        ],
        [
            'name' => Inventory::SUV->value,
            'label' => "SUV",
        ],
        [
            'name' => Inventory::COASTER->value,
            'label' => "Coaster",
        ],
        [
            'name' => Inventory::EBIKE->value,
            'label' => "E-Bike",
        ],
        [
            'name' => Inventory::BIKE->value,
            'label' => "Bike",
        ],
        [
            'name' => Inventory::TRACTOR->value,
            'label' => "Tractor",
        ],
    ],
    'event_halls' => [
        [
            'name' => Inventory::PLENARY->value,
            'label' => "Plenary Hall",
        ],
        [
            'name' => Inventory::TRAININGROOM->value,
            'label' => "Training Room",
        ],
        [
            'name' => Inventory::MPH->value,
            'label' => "Multi-Purpose Hall",
        ],
    ],
    'storage_locations' => [
        [
            'name' => Inventory::ROI->value,
            'label' => "Researchers Office I",
        ],
        [
            'name' => Inventory::ROII->value,
            'label' =>  "Researchers Office II",
        ],
        [
            'name' => Inventory::BIOINFOROOM->value,
            'label' =>  "Bioinformatics Room",
        ],
        [
            'name' => Inventory::MOLECULARGENETICSROOM->value,
            'label' => "Molecular Genetics Room",
        ],
        [
            'name' => Inventory::GENETICTRANSROOM->value,
            'label' => "Genome Enineering Laboratory",
        ],
        [
            'name' => Inventory::TISSUECULTUREROOM->value,
            'label' => "Tissue Culture Room",
        ],
        [
            'name' => Inventory::SYSTEMBIOLOGYROOM->value,
            'label' => "Systems Biology Room",
        ],
        [
            'name' => Inventory::MICROBIALBIOTECHROOM->value,
            'label' => "Microbial Biotechnology Room",
        ],
        [
            'name' => Inventory::MOLECULARDIAGNOSTICSROOM->value,
            'label' => "Molecular Diagnostics Room",
        ],
        [
            'name' => Inventory::DIAGNOSTICLAB->value,
            'label' => "Diagnostic Laboratory",
        ],
        [
            'name' => Inventory::PHENOTYPINGAREA->value,
            'label' => "Phenotyping Area",
        ],
        [
            'name' => Inventory::MICROSCOPESEQUENCEROOM->value,
            'label' => "Microscope and Sequencing Room",
        ],
        [
            'name' => Inventory::GENERALEQUIPMENTAREA->value,
            'label' => "General Equipment Area",
        ],
        [
            'name' => Inventory::SAMPLEPROCESSINGROOM->value,
            'label' => "Sample Processing Room",
        ],
        [
            'name' => Inventory::WASHROOMI->value,
            'label' => "Wash Room I",
        ],
        [
            'name' => Inventory::WASHROOMII->value,
            'label' => "Wash Room II",
        ],
        [
            'name' => Inventory::CENTRALBODEGA->value,
            'label' => "Central Bodega",
        ],
        [
            'name' => Inventory::BODEGAONE->value,
            'label' => "Bodega 1",
        ],
        [
            'name' => Inventory::BODEGATWO->value,
            'label' => "Bodega 2",
        ],
        [
            'name' => Inventory::PLENARY->value,
            'label' => "Plenary Hall",
        ],
        [
            'name' => Inventory::TRAININGROOM->value,
            'label' => "Training Room",
        ],
        [
            'name' => Inventory::MPH->value,
            'label' => "Multi-Purpose Hall",
        ],
        [
            'name' => Inventory::MOTORPOOL->value,
            'label' => "Motor Pool",
        ],
        [
            'name' => Inventory::AADELACRUZ->value,
            'label' => "AADelaCruz Office",
        ],
        [
            'name' => Inventory::RRSURALTA->value,
            'label' => "RRSuralta Office",
        ],
        [
            'name' => Inventory::AAOFFICE->value,
            'label' => "AA Office",
        ],
        [
            'name' => Inventory::MEETINGROOM->value,
            'label' => "Meeting Room",
        ],
        [
            'name' => Inventory::SUPPLIESROOM1->value,
            'label' => "Supplies Room I",
        ],
        [
            'name' => Inventory::SUPPLIESROOM2->value,
            'label' => "Supplies Room II",
        ],
        [
            'name' => Inventory::CONSULTANTOFFICE->value,
            'label' => "Consultant Office",
        ],
        [
            'name' => Inventory::DARKROOM->value,
            'label' => "Dark Room",
        ],
        [
            'name' => Inventory::DNAEXTRACTIONROOM->value,
            'label' => "DNA Extraction Room",
        ],
        [
            'name' => Inventory::FREEZERROOM->value,
            'label' => "Freezer Room",
        ],
        [
            'name' => Inventory::LIGHTROOM->value,
            'label' => "Light Room",
        ],
        [
            'name' => Inventory::SCREENHOUSE1->value,
            'label' => "Screenhouse 1",
        ]
    ]
];
