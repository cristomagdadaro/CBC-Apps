<?php

use App\Enums\Inventory;

return [
    'approving_officers' => 'CRISTO REY C. MAGDADARO',
    'center_chief' => 'ROEL R. SURALTA',
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
    ],
    'evemt_halls' => [
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
    ]
];
