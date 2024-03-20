<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Tasks\Controller\ApiController;
use Modules\Tasks\Models\PermissionCategory;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^.*/task(\?.*|$)' => [
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TASK,
            ],
        ],
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskSet',
            'verb'       => RouteVerb::SET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionCategory::TASK,
            ],
        ],
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskGet',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TASK,
            ],
        ],
    ],
    '^.*/task/reminder(\?.*|$)' => [
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskReminderCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::TASK,
            ],
        ],
    ],
    '^.*/task/element(\?.*$|$)' => [
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskElementCreate',
            'verb'       => RouteVerb::PUT,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::CREATE,
                'state'  => PermissionCategory::ELEMENT,
            ],
        ],
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskElementSet',
            'verb'       => RouteVerb::SET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::MODIFY,
                'state'  => PermissionCategory::ELEMENT,
            ],
        ],
        [
            'dest'       => '\Modules\Tasks\Controller\ApiController:apiTaskElementGet',
            'verb'       => RouteVerb::GET,
            'permission' => [
                'module' => ApiController::NAME,
                'type'   => PermissionType::READ,
                'state'  => PermissionCategory::TASK,
            ],
        ],
    ],
];
