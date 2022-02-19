<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Tasks\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Models;

/**
 * Task template class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
 * @since   1.0.0
 */
class TaskTemplate extends Task
{
    /**
     * Type.
     *
     * @var int
     * @since 1.0.0
     */
    protected int $type = TaskType::TEMPLATE;
}
