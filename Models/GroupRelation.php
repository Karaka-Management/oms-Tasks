<?php
/**
 * Jingga
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

use Modules\Admin\Models\Group;
use Modules\Admin\Models\NullGroup;

/**
 * Task relation to group
 *
 * @package Modules\Tasks\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class GroupRelation extends RelationAbstract
{
    /**
     * Relation object
     *
     * @var Group
     * @since 1.0.0
     */
    public Group $relation;

    /**
     * Constructor.
     *
     * @param null|Group $group Group
     * @param int        $duty  Duty type
     *
     * @since 1.0.0
     */
    public function __construct(Group $group = null, int $duty = DutyType::TO)
    {
        $this->relation = $group ?? new NullGroup();
        $this->duty     = $duty;
    }

    /**
     * Get relation object.
     *
     * @return Group
     *
     * @since 1.0.0
     */
    public function getRelation() : Group
    {
        return $this->relation;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'       => $this->id,
            'duty'     => $this->duty,
            'relation' => $this->relation,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() : mixed
    {
        return $this->toArray();
    }
}
