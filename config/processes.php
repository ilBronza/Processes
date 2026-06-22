<?php

use App\Models\User;
use IlBronza\Processes\Http\Controllers\Parameters\Datatables\ProcessFieldsGroupParametersFile;
use IlBronza\Processes\Http\Controllers\Parameters\Datatables\ProcessStatusFieldsGroupParametersFile;
use IlBronza\Processes\Http\Controllers\Parameters\Datatables\ProcessStatusLogFieldsGroupParametersFile;
use IlBronza\Processes\Http\Controllers\Parameters\Datatables\ProcessStatusTransitionFieldsGroupParametersFile;
use IlBronza\Processes\Http\Controllers\Parameters\Datatables\ProcessTypeFieldFieldsGroupParametersFile;
use IlBronza\Processes\Http\Controllers\Parameters\Datatables\ProcessTypeFieldsGroupParametersFile;
use IlBronza\Processes\Http\Controllers\Parameters\Fieldsets\ProcessFieldsetsParameters;
use IlBronza\Processes\Http\Controllers\Parameters\Fieldsets\ProcessStatusFieldsetsParameters;
use IlBronza\Processes\Http\Controllers\Parameters\Fieldsets\ProcessStatusTransitionFieldsetsParameters;
use IlBronza\Processes\Http\Controllers\Parameters\Fieldsets\ProcessTypeFieldFieldsetsParameters;
use IlBronza\Processes\Http\Controllers\Parameters\Fieldsets\ProcessTypeFieldsetsParameters;
use IlBronza\Processes\Http\Controllers\Processes\ProcessCrudController;
use IlBronza\Processes\Http\Controllers\ProcessStatuses\ProcessStatusCrudController;
use IlBronza\Processes\Http\Controllers\ProcessStatusLogs\ProcessStatusLogCrudController;
use IlBronza\Processes\Http\Controllers\ProcessStatusTransitions\ProcessStatusTransitionCrudController;
use IlBronza\Processes\Http\Controllers\ProcessTypeFields\ProcessTypeFieldCrudController;
use IlBronza\Processes\Http\Controllers\ProcessTypes\ProcessTypeCrudController;
use IlBronza\Processes\Models\Process;
use IlBronza\Processes\Models\ProcessStatus;
use IlBronza\Processes\Models\ProcessStatusLog;
use IlBronza\Processes\Models\ProcessStatusTransition;
use IlBronza\Processes\Models\ProcessType;
use IlBronza\Processes\Models\ProcessTypeField;

return [
    'routePrefix' => 'ibProcesses.',
    'route_prefix' => 'processes-management',

    'defaultRoles' => [
        'superadmin',
        'administrator',
        'processes',
    ],

    'routeRoles' => [],

    'middleware' => ['web', 'auth', 'processes.roles'],

    'models' => [
        'process' => [
            'class' => Process::class,
            'table' => 'processes__processes',
            'controllers' => [
                'index' => ProcessCrudController::class,
                'create' => ProcessCrudController::class,
                'store' => ProcessCrudController::class,
                'show' => ProcessCrudController::class,
                'edit' => ProcessCrudController::class,
                'update' => ProcessCrudController::class,
                'destroy' => ProcessCrudController::class,
            ],
            'fieldsGroupsFiles' => [
                'index' => ProcessFieldsGroupParametersFile::class,
                'related' => ProcessFieldsGroupParametersFile::class,
            ],
            'parametersFiles' => [
                'create' => ProcessFieldsetsParameters::class,
                'edit' => ProcessFieldsetsParameters::class,
            ],
        ],
        'processType' => [
            'class' => ProcessType::class,
            'table' => 'processes__process_types',
            'controllers' => [
                'index' => ProcessTypeCrudController::class,
                'create' => ProcessTypeCrudController::class,
                'store' => ProcessTypeCrudController::class,
                'show' => ProcessTypeCrudController::class,
                'edit' => ProcessTypeCrudController::class,
                'update' => ProcessTypeCrudController::class,
                'destroy' => ProcessTypeCrudController::class,
            ],
            'fieldsGroupsFiles' => [
                'index' => ProcessTypeFieldsGroupParametersFile::class,
                'related' => ProcessTypeFieldsGroupParametersFile::class,
            ],
            'parametersFiles' => [
                'create' => ProcessTypeFieldsetsParameters::class,
                'edit' => ProcessTypeFieldsetsParameters::class,
            ],
        ],
        'processStatus' => [
            'class' => ProcessStatus::class,
            'table' => 'processes__process_statuses',
            'controllers' => [
                'index' => ProcessStatusCrudController::class,
                'create' => ProcessStatusCrudController::class,
                'store' => ProcessStatusCrudController::class,
                'show' => ProcessStatusCrudController::class,
                'edit' => ProcessStatusCrudController::class,
                'update' => ProcessStatusCrudController::class,
                'destroy' => ProcessStatusCrudController::class,
            ],
            'fieldsGroupsFiles' => [
                'index' => ProcessStatusFieldsGroupParametersFile::class,
                'related' => ProcessStatusFieldsGroupParametersFile::class,
            ],
            'parametersFiles' => [
                'create' => ProcessStatusFieldsetsParameters::class,
                'edit' => ProcessStatusFieldsetsParameters::class,
            ],
        ],
        'processStatusLog' => [
            'class' => ProcessStatusLog::class,
            'table' => 'processes__process_status_logs',
            'controllers' => [
                'index' => ProcessStatusLogCrudController::class,
                'show' => ProcessStatusLogCrudController::class,
            ],
            'fieldsGroupsFiles' => [
                'index' => ProcessStatusLogFieldsGroupParametersFile::class,
                'related' => ProcessStatusLogFieldsGroupParametersFile::class,
            ],
        ],
        'processStatusTransition' => [
            'class' => ProcessStatusTransition::class,
            'table' => 'processes__process_status_transitions',
            'controllers' => [
                'index' => ProcessStatusTransitionCrudController::class,
                'create' => ProcessStatusTransitionCrudController::class,
                'store' => ProcessStatusTransitionCrudController::class,
                'show' => ProcessStatusTransitionCrudController::class,
                'edit' => ProcessStatusTransitionCrudController::class,
                'update' => ProcessStatusTransitionCrudController::class,
                'destroy' => ProcessStatusTransitionCrudController::class,
            ],
            'fieldsGroupsFiles' => [
                'index' => ProcessStatusTransitionFieldsGroupParametersFile::class,
                'related' => ProcessStatusTransitionFieldsGroupParametersFile::class,
            ],
            'parametersFiles' => [
                'create' => ProcessStatusTransitionFieldsetsParameters::class,
                'edit' => ProcessStatusTransitionFieldsetsParameters::class,
            ],
        ],
        'processTypeField' => [
            'class' => ProcessTypeField::class,
            'table' => 'processes__process_type_fields',
            'controllers' => [
                'index' => ProcessTypeFieldCrudController::class,
                'create' => ProcessTypeFieldCrudController::class,
                'store' => ProcessTypeFieldCrudController::class,
                'show' => ProcessTypeFieldCrudController::class,
                'edit' => ProcessTypeFieldCrudController::class,
                'update' => ProcessTypeFieldCrudController::class,
                'destroy' => ProcessTypeFieldCrudController::class,
            ],
            'fieldsGroupsFiles' => [
                'index' => ProcessTypeFieldFieldsGroupParametersFile::class,
                'related' => ProcessTypeFieldFieldsGroupParametersFile::class,
            ],
            'parametersFiles' => [
                'create' => ProcessTypeFieldFieldsetsParameters::class,
                'edit' => ProcessTypeFieldFieldsetsParameters::class,
            ],
        ],
        'user' => [
            'class' => User::class,
            'table' => 'users',
        ],
        'operator' => [
            'class' => \IlBronza\Operators\Models\Operator::class,
            'table' => 'operators__operators',
        ],
    ],

    'behavior' => [
        'prevent_direct_status_update' => true,
        'prevent_locked_update' => true,
        'create_initial_status_log' => true,
        'validate_transitions' => true,
        'validate_overlaps' => true,
        'allow_operator_overlaps_default' => false,
        'allow_tool_overlaps_default' => false,
        'morph_id_type' => 'uuid',
    ],

    'crud' => [
        'enabled' => true,
        'logs_readonly' => true,
    ],

    'defaults' => [
        'statuses' => [
            ['name' => 'draft', 'label' => 'Draft', 'is_initial' => true],
            ['name' => 'planned', 'label' => 'Planned'],
            ['name' => 'started', 'label' => 'Started', 'is_blocking' => true],
            ['name' => 'paused', 'label' => 'Paused'],
            ['name' => 'completed', 'label' => 'Completed', 'is_final' => true, 'is_positive' => true],
            ['name' => 'cancelled', 'label' => 'Cancelled', 'is_final' => true, 'is_negative' => true],
            ['name' => 'failed', 'label' => 'Failed', 'is_final' => true, 'is_negative' => true],
            ['name' => 'approved', 'label' => 'Approved', 'is_positive' => true],
            ['name' => 'rejected', 'label' => 'Rejected', 'is_negative' => true],
            ['name' => 'locked', 'label' => 'Locked', 'is_blocking' => true],
        ],
        'types' => [
            ['name' => 'production', 'label' => 'Production', 'requires_operator' => true],
            ['name' => 'pause', 'label' => 'Pause', 'allows_overlaps' => true],
            ['name' => 'maintenance', 'label' => 'Maintenance', 'requires_tool' => true],
            ['name' => 'travel', 'label' => 'Travel', 'requires_operator' => true],
            ['name' => 'setup', 'label' => 'Setup', 'requires_operator' => true],
            ['name' => 'testing', 'label' => 'Testing'],
            ['name' => 'clock_in', 'label' => 'Clock in', 'requires_operator' => true],
            ['name' => 'clock_out', 'label' => 'Clock out', 'requires_operator' => true],
            ['name' => 'absence', 'label' => 'Absence', 'requires_operator' => true],
            ['name' => 'machine_downtime', 'label' => 'Machine downtime', 'requires_tool' => true],
        ],
    ],
];
