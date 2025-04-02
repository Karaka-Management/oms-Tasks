<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Tasks\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Models;

use Modules\Admin\Models\Account;

/**
 * Null model
 *
 * @package Modules\Tasks\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
class TaskSeen
{
    /**
     * Seen ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    public \DateTime $seenAt;

    public int $seenBy = 0;

    public int $task = 0;

    public ?\DateTime $reminderAt = null;

    public bool $isRemindered = false;

    public ?Account $reminderBy = null;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->seenAt = new \DateTime('now');
    }
}
