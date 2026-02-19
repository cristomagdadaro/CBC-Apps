<?php

use App\Enums\Role;

return [
    'permissions' => [
        'fes.request.approve',
        'laboratory.logger.manage',
        'inventory.manage',
        'equipment.report.manage',
        'event.forms.manage',
        'users.manage',
        'rental.vehicle.manage',
        'rental.venue.manage',
        'rental.hostel.manage',
    ],

    'role_permissions' => [
        Role::ADMIN->value => ['*'],

        Role::LABORATORY_MANAGER->value => [
            'fes.request.approve',
            'laboratory.logger.manage',
            'inventory.manage',
            'equipment.report.manage',
        ],

        Role::ICT_MANAGER->value => [
            'inventory.manage',
            'equipment.report.manage',
            'event.forms.manage',
        ],

        Role::ADMINISTRATIVE_ASSISTANT->value => [
            'rental.vehicle.manage',
            'rental.venue.manage',
            'rental.hostel.manage',
        ],
    ],
];
