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

use phpOMS\Localization\ISO639x1Enum;

/**
 * Task attribute value class.
 *
 * The relation with the type/task is defined in the TaskAttribute class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class TaskAttributeValue implements \JsonSerializable
{
    /**
     * Id
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Datatype of the attribute
     *
     * @var int
     * @since 1.0.0
     */
    public int $type = 0;

    /**
     * Int value
     *
     * @var null|int
     * @since 1.0.0
     */
    public ?int $valueInt = null;

    /**
     * String value
     *
     * @var null|string
     * @since 1.0.0
     */
    public ?string $valueStr = null;

    /**
     * Decimal value
     *
     * @var null|float
     * @since 1.0.0
     */
    public ?float $valueDec = null;

    /**
     * DateTime value
     *
     * @var null|\DateTimeInterface
     * @since 1.0.0
     */
    public ?\DateTimeInterface $valueDat = null;

    /**
     * Is a default value which can be selected
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $isDefault = false;

    /**
     * Unit of the value
     *
     * @var string
     * @since 1.0.0
     */
    public string $unit = '';

    /**
     * Localization
     *
     * @var null|TaskAttributeValueL11n
     */
    private ?TaskAttributeValueL11n $l11n = null;

    /**
     * Constructor.
     *
     * @param int   $type  Type
     * @param mixed $value Value
     *
     * @since 1.0.0
     */
    public function __construct(int $type = 0, mixed $value = '')
    {
        $this->type = $type;

        $this->setValue($value);
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
     * Set l11n
     *
     * @param string|TaskAttributeValueL11n $l11n Tag article l11n
     * @param string                        $lang Language
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setL11n(string | TaskAttributeValueL11n $l11n, string $lang = ISO639x1Enum::_EN) : void
    {
        if ($l11n instanceof TaskAttributeValueL11n) {
            $this->l11n = $l11n;
        } elseif (isset($this->l11n) && $this->l11n instanceof TaskAttributeValueL11n) {
            $this->l11n->title = $l11n;
        } else {
            $this->l11n        = new TaskAttributeValueL11n();
            $this->l11n->title = $l11n;
            $this->l11n->setLanguage($lang);
        }
    }

    /**
     * Get localization
     *
     * @return null|string
     *
     * @since 1.0.0
     */
    public function getL11n() : ?string
    {
        return $this->l11n instanceof TaskAttributeValueL11n ? $this->l11n->title : $this->l11n;
    }

    /**
     * Set value
     *
     * @param int|string|float|\DateTimeInterface $value Value
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setValue(mixed $value) : void
    {
        if (\is_string($value)) {
            $this->valueStr = $value;
        } elseif (\is_int($value)) {
            $this->valueInt = $value;
        } elseif (\is_float($value)) {
            $this->valueDec = $value;
        } elseif ($value instanceof \DateTimeInterface) {
            $this->valueDat = $value;
        }
    }

    /**
     * Get value
     *
     * @return null|int|string|float|\DateTimeInterface
     *
     * @since 1.0.0
     */
    public function getValue() : mixed
    {
        if (!empty($this->valueStr)) {
            return $this->valueStr;
        } elseif (!empty($this->valueInt)) {
            return $this->valueInt;
        } elseif (!empty($this->valueDec)) {
            return $this->valueDec;
        } elseif ($this->valueDat instanceof \DateTimeInterface) {
            return $this->valueDat;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'        => $this->id,
            'type'      => $this->type,
            'valueInt'  => $this->valueInt,
            'valueStr'  => $this->valueStr,
            'valueDec'  => $this->valueDec,
            'valueDat'  => $this->valueDat,
            'isDefault' => $this->isDefault,
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
