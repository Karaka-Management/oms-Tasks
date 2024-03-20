<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Tasks\Models\Attribute
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Models\Attribute;

use Modules\Attribute\Models\AttributeType;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Task mapper class.
 *
 * @package Modules\Tasks\Models\Attribute
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of AttributeType
 * @extends DataMapperFactory<T>
 */
final class TaskAttributeTypeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'task_attr_type_id'         => ['name' => 'task_attr_type_id',       'type' => 'int',    'internal' => 'id'],
        'task_attr_type_name'       => ['name' => 'task_attr_type_name',     'type' => 'string', 'internal' => 'name', 'autocomplete' => true],
        'task_attr_type_datatype'   => ['name' => 'task_attr_type_datatype',   'type' => 'int',    'internal' => 'datatype'],
        'task_attr_type_fields'     => ['name' => 'task_attr_type_fields',   'type' => 'int',    'internal' => 'fields'],
        'task_attr_type_custom'     => ['name' => 'task_attr_type_custom',   'type' => 'bool',   'internal' => 'custom'],
        'task_attr_type_repeatable' => ['name' => 'task_attr_type_repeatable',   'type' => 'bool',   'internal' => 'repeatable'],
        'task_attr_type_internal'   => ['name' => 'task_attr_type_internal',   'type' => 'bool',   'internal' => 'isInternal'],
        'task_attr_type_pattern'    => ['name' => 'task_attr_type_pattern',  'type' => 'string', 'internal' => 'validationPattern'],
        'task_attr_type_required'   => ['name' => 'task_attr_type_required', 'type' => 'bool',   'internal' => 'isRequired'],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'l11n' => [
            'mapper'   => TaskAttributeTypeL11nMapper::class,
            'table'    => 'task_attr_type_l11n',
            'self'     => 'task_attr_type_l11n_type',
            'column'   => 'content',
            'external' => null,
        ],
        'defaults' => [
            'mapper'   => TaskAttributeValueMapper::class,
            'table'    => 'task_attr_default',
            'self'     => 'task_attr_default_type',
            'external' => 'task_attr_default_value',
        ],
    ];

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = AttributeType::class;

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'task_attr_type';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'task_attr_type_id';
}
