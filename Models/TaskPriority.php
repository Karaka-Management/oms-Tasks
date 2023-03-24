<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Tasks\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Models;

use phpOMS\Stdlib\Base\Enum;

/**
 * Task priority enum.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class TaskPriority extends Enum
{
    public const NONE = 0;

    public const VLOW = 1;

    public const LOW = 2;

    public const MEDIUM = 3;

    public const HIGH = 4;

    public const VHIGH = 5;
}
