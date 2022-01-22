<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Tasks\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Tasks\Models;

/**
 * Null model
 *
 * @package Modules\Tasks\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
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
    protected int $id = 0;

    public \DateTime $seenAt;

    public int $seenBy = 0;

    public int $task = 0;

    /**
     * The flag allows to set a task as not seen even it was already seen.
     *
     * This is helpful for changes to a task or forwarding which should be signaled to the user.
     * Another situation could be if a user wants to mark a task as unseen in order to check it out later on again.
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $flag = false;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->seenAt = new \DateTime('now');
    }

    /**
     * Get id
     *
     * @return int
     *
     * @since 1.0.0
     */
    public function getId() : int
    {
        return $this->id;
    }
}
