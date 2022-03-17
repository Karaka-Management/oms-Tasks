<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Tasks\Admin\Install
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Admin\Install;

use phpOMS\Application\ApplicationAbstract;

/**
 * Dashboard class.
 *
 * @package Modules\Tasks\Admin\Install
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
class Dashboard
{
    /**
     * Install dashboard providing
     *
     * @param string              $path Module path
     * @param ApplicationAbstract $app  Application
     *
     * @return void
     *
     * @since 1.0.0
     */
    public static function install(string $path, ApplicationAbstract $app) : void
    {
        \Modules\Dashboard\Admin\Installer::installExternal($app, ['path' => __DIR__ . '/Dashboard.install.json']);
    }
}
