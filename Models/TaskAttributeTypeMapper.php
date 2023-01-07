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

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Task mapper class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
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
        'task_attr_type_id'       => ['name' => 'task_attr_type_id',       'type' => 'int',    'internal' => 'id'],
        'task_attr_type_name'     => ['name' => 'task_attr_type_name',     'type' => 'string', 'internal' => 'name', 'autocomplete' => true],
        'task_attr_type_fields'   => ['name' => 'task_attr_type_fields',   'type' => 'int',    'internal' => 'fields'],
        'task_attr_type_custom'   => ['name' => 'task_attr_type_custom',   'type' => 'bool',   'internal' => 'custom'],
        'task_attr_type_pattern'  => ['name' => 'task_attr_type_pattern',  'type' => 'string', 'internal' => 'validationPattern'],
        'task_attr_type_required' => ['name' => 'task_attr_type_required', 'type' => 'bool',   'internal' => 'isRequired'],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'l11n' => [
            'mapper'   => TaskAttributeTypeL11nMapper::class,
            'table'    => 'task_attr_type_l11n',
            'self'     => 'task_attr_type_l11n_type',
            'column'   => 'title',
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
    public const PRIMARYFIELD ='task_attr_type_id';
}
