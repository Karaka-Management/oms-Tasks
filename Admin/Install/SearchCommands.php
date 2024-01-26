<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Tasks\Controller\SearchController;
use phpOMS\Account\PermissionType;
use phpOMS\Router\RouteVerb;

return [
    '^:tag (\?.*$|$)' => [
        [
            'dest'       => '\Modules\Tasks\Controller\SearchController:searchTag',
            'verb'       => RouteVerb::ANY,
            'permission' => [
                'module' => SearchController::NAME,
                'type'   => PermissionType::READ,
            ],
        ],
    ],
];
