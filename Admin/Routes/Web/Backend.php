<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use Modules\Tasks\Controller\BackendController;
use Modules\Tasks\Models\PermissionState;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/task/dashboard.*$' => [
        [
            'dest'       => '\Modules\Tasks\Controller\BackendController:viewTaskDashboard',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::TASK,
            ],
        ],
    ],
    '^.*/task/single.*$' => [
        [
            'dest'       => '\Modules\Tasks\Controller\BackendController:viewTaskView',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::TASK,
            ],
        ],
    ],
    '^.*/task/create.*$' => [
        [
            'dest'       => '\Modules\Tasks\Controller\BackendController:viewTaskCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::TASK,
            ],
        ],
    ],
    '^.*/task/analysis.*$' => [
        [
            'dest'       => '\Modules\Tasks\Controller\BackendController:viewTaskAnalysis',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::ANALYSIS,
            ],
        ],
    ],
];
