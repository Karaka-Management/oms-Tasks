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

use Modules\Tasks\Controller\ApiController;
use Modules\Tasks\Models\PermissionState;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/task(\?.*|$)' => [
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::TASK,
            ],
        ],
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskSet',
            'verb'       => RouteVerb::SET,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionState::TASK,
            ],
        ],
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskGet',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::TASK,
            ],
        ],
    ],
    '^.*/task/element.*$' => [
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskElementCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionState::ELEMENT,
            ],
        ],
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskElementSet',
            'verb'       => RouteVerb::SET,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionState::ELEMENT,
            ],
        ],
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskElementGet',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => ApiController::MODULE_NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionState::TASK,
            ],
        ],
    ],
];
