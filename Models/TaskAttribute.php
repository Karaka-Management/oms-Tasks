<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Tasks\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Models;

/**
 * Task class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class TaskAttribute implements \JsonSerializable
{
    /**
     * Id.
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Task this attribute belongs to
     *
     * @var int
     * @since 1.0.0
     */
    public int $task = 0;

    /**
     * Attribute type the attribute belongs to
     *
     * @var TaskAttributeType
     * @since 1.0.0
     */
    public TaskAttributeType $type;

    /**
     * Attribute value the attribute belongs to
     *
     * @var TaskAttributeValue
     * @since 1.0.0
     */
    public TaskAttributeValue $value;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->type  = new NullTaskAttributeType();
        $this->value = new NullTaskAttributeValue();
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

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'    => $this->id,
            'task'  => $this->task,
            'type'  => $this->type,
            'value' => $this->value,
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
