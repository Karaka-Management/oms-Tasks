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
 * Task Attribute Type class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
class TaskAttributeType implements \JsonSerializable
{
    /**
     * Id
     *
     * @var int
     * @since 1.0.0
     */
    protected int $id = 0;

    /**
     * Name/string identifier by which it can be found/categorized
     *
     * @var string
     * @since 1.0.0
     */
    public string $name = '';

    /**
     * Which field data type is required (string, int, ...) in the value
     *
     * @var int
     * @since 1.0.0
     */
    protected int $fields = 0;

    /**
     * Is a custom value allowed (e.g. custom string)
     *
     * @var bool
     * @since 1.0.0
     */
    public bool $custom = false;

    public string $validationPattern = '';

    public bool $isRequired = false;

    /**
     * Localization
     *
     * @var TaskAttributeTypeL11n
     */
    private string | TaskAttributeTypeL11n $l11n = '';

    /**
     * Possible default attribute values
     *
     * @var array
     */
    private array $defaults = [];

    /**
     * Default attribute value
     *
     * @var int
     * @since 1.0.0
     */
    public int $default = 0;

    /**
     * Constructor.
     *
     * @param string $name Name/identifier of the attribute type
     *
     * @since 1.0.0
     */
    public function __construct(string $name = '')
    {
        $this->name = $name;
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
     * @param string|TaskAttributeTypeL11n $l11n Tag article l11n
     * @param string                       $lang Language
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setL11n(string | TaskAttributeTypeL11n $l11n, string $lang = ISO639x1Enum::_EN) : void
    {
        if ($l11n instanceof TaskAttributeTypeL11n) {
            $this->l11n = $l11n;
        } elseif (isset($this->l11n) && $this->l11n instanceof TaskAttributeTypeL11n) {
            $this->l11n->title = $l11n;
        } else {
            $this->l11n        = new TaskAttributeTypeL11n();
            $this->l11n->title = $l11n;
            $this->l11n->setLanguage($lang);
        }
    }

    /**
     * @return string
     *
     * @since 1.0.0
     */
    public function getL11n() : string
    {
        return $this->l11n instanceof TaskAttributeTypeL11n ? $this->l11n->title : $this->l11n;
    }

    /**
     * Set fields
     *
     * @param int $fields Fields
     *
     * @return void
     *
     * @since 1.0.0
     */
    public function setFields(int $fields) : void
    {
        $this->fields = $fields;
    }

    /**
     * Get default values
     *
     * @return array
     *
     * @sicne 1.0.0
     */
    public function getDefaults() : array
    {
        return $this->defaults;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray() : array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'validationPattern' => $this->validationPattern,
            'custom'            => $this->custom,
            'isRequired'        => $this->isRequired,
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
