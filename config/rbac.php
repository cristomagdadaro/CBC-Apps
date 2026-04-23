<?php

use App\Enums\Role;

return [
    'permissions' => [
        'fes.request.approve',
        'laboratory.logger.manage',
        'inventory.manage',
        'equipment.report.manage',
        'event.forms.manage',
        'event.certificates.manage',
        'users.manage',
        'rental.vehicle.manage',
        'rental.venue.manage',
        'rental.hostel.manage',
        'rental.request.approve',
        'research.dashboard.view',
        'research.projects.view',
        'research.projects.create',
        'research.projects.update',
        'research.projects.delete',
        'research.studies.manage',
        'research.experiments.manage',
        'research.samples.manage',
        'research.monitoring.manage',
        'research.exports.manage',
        'golinks.manage',
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
            'event.certificates.manage',
        ],

        Role::ADMINISTRATIVE_ASSISTANT->value => [
            'event.certificates.manage',
            'rental.vehicle.manage',
            'rental.venue.manage',
            'rental.hostel.manage',
            'rental.request.approve',
        ],

        Role::RESEARCHER->value => [
            'research.dashboard.view',
            'research.projects.view',
            'research.projects.create',
            'research.projects.update',
            'research.studies.manage',
            'research.experiments.manage',
            'research.samples.manage',
            'research.monitoring.manage',
        ],

        Role::RESEARCH_SUPERVISOR->value => [
            'research.dashboard.view',
            'research.projects.view',
            'research.projects.create',
            'research.projects.update',
            'research.projects.delete',
            'research.studies.manage',
            'research.experiments.manage',
            'research.samples.manage',
            'research.monitoring.manage',
            'research.exports.manage',
        ],
    ],
];
