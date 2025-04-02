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
use Modules\Admin\Models\Group;

/**
 * Task relation to accounts or groups
 *
 * @package Modules\Tasks\Models
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
abstract class RelationAbstract implements \JsonSerializable
{
    /**
     * ID.
     *
     * @var int
     * @since 1.0.0
     */
    public int $id = 0;

    /**
     * Duty.
     *
     * @var int
     * @since 1.0.0
     */
    public int $duty = DutyType::TO;

    /**
     * Element id.
     *
     * @var int
     * @since 1.0.0
     */
    public int $element = 0;

    /**
     * Set the duty (TO or CC)
     *
     * @param int $duty Is TO or CC
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setDuty(int $duty) : void
    {
        $this->duty = $duty;
    }

    /**
     * Get the duty (TO or CC)
     *
     * @return int Is TO or CC
     *
     * @since 1.0.0
     */
    public function getDuty() : int
    {
        return $this->duty;
    }

    /**
     * Get the relation object
     *
     * @return Account|Group
     *
     * @since 1.0.0
     */
    abstract public function getRelation();

    /**
     * {@inheritdoc}
     */
    abstract public function jsonSerialize() : mixed;
}
