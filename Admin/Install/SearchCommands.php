<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use Modules\Tasks\Controller\SearchController;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^:tag .*$' => [
        [
            'dest'       => '\Modules\Tasks\Controller\SearchController:searchTags',
            'verb'       => RouteVerb::ANY,
            'permission' => [
                'module' => SearchController::NAME,
                'type'   => PermissionType::READ,
            ],
        ],
    ],
];
