<?php

use App\Enums\Inventory;

return [
    'approving_officers' => 'CRISTO REY C. MAGDADARO',
    'center_chief' => 'ROEL R. SURALTA',
    'transaction_type' => [
        Inventory::INCOMING->value,
        Inventory::OUTGOING->value,
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
            'label' => "Genetic Transformation Room",
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
    ]
];
