<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Tasks\Controller\BackendController;
use Modules\Tasks\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/task/dashboard(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Tasks\Controller\BackendController:viewTaskDashboard',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TASK,
            ],
        ],
    ],
    '^.*/task/view(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Tasks\Controller\BackendController:viewTaskView',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TASK,
            ],
        ],
    ],
    '^.*/task/create(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Tasks\Controller\BackendController:viewTaskCreate',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TASK,
            ],
        ],
    ],
    '^.*/task/analysis(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Tasks\Controller\BackendController:viewTaskAnalysis',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => BackendController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::ANALYSIS,
            ],
        ],
    ],
];
