<?php

return [
    'commodities' => [
        'Rice',
        'Coconut',
        'Banana',
        'Abaca',
        'Tomato',
        'Corn',
        'Rubber Tree',
        'Garlic',
        'Mushroom',
        'Bacteria',
        'Virus',
        'Leaf Tissue',
        'Seeds',
    ],

    'sample_types' => [
        'Leaf Tissue',
        'Bacteria',
        'Virus',
        'Seeds',
        'Root Tissue',
        'Stem Tissue',
        'DNA Extract',
        'Soil',
        'Water',
        'Other',
    ],

    'seasons' => [
        ['value' => 'wet', 'label' => 'Wet Season'],
        ['value' => 'dry', 'label' => 'Dry Season'],
    ],

    'stages' => [
        'germination' => [
            'label' => 'Seed Selection and Germination',
            'description' => 'Capture germination dates, line counts, and plants generated per line.',
            'suggested_parameters' => [
                'cross_combination',
                'line_id',
                'plants_generated',
                'germination_rate',
            ],
        ],
        'sowing' => [
            'label' => 'Sowing',
            'description' => 'Record sowing date, surviving plants, and plant naming after reduction.',
            'suggested_parameters' => [
                'surviving_plants',
                'renamed_plant_ids',
                'days_after_sowing',
            ],
        ],
        'agro_morphology' => [
            'label' => 'Agro-Morphological Data',
            'description' => 'Store per-plant measurements such as height, tiller count, panicle count, and scoring.',
            'suggested_parameters' => [
                'plant_height_cm',
                'tiller_count',
                'panicle_count',
                'heading_date',
                'stomatal_conductance',
                'leaf_scoring',
            ],
        ],
        'post_harvest' => [
            'label' => 'Post-Harvest Processing',
            'description' => 'Capture filled and unfilled seed counts, weights, and selection markers for export.',
            'suggested_parameters' => [
                'filled_seed_count',
                'unfilled_seed_count',
                'filled_seed_weight_g',
                'unfilled_seed_weight_g',
                'selected_for_keep',
            ],
        ],
        'environment' => [
            'label' => 'Environmental Conditions',
            'description' => 'Track piezometer logs, SMC, drought windows, rainfall, pests, disease, and fertilizer applications.',
            'suggested_parameters' => [
                'piezometer_log',
                'smc',
                'drought_start',
                'drought_end',
                'rain_occurrence',
                'pest_or_disease',
                'fertilizer_application',
            ],
        ],
    ],

    'permission_matrix' => [
        [
            'role' => 'Researcher',
            'permissions' => [
                'View research dashboard and project portfolio',
                'Create and update projects assigned to the module',
                'Manage studies, experiments, samples, and monitoring records',
                'Maintain readable field references alongside barcode-safe sample IDs',
            ],
        ],
        [
            'role' => 'Research Supervisor',
            'permissions' => [
                'Everything a researcher can do',
                'Delete projects when cleanup or retirement is required',
                'Export experiment monitoring data to spreadsheet-ready CSV',
                'Override module-level operational decisions through elevated permissions',
            ],
        ],
    ],
];
