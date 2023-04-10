<?php
/**
 * Karaka
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

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Task mapper class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of TaskAttributeValue
 * @extends DataMapperFactory<T>
 */
final class TaskAttributeValueMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'task_attr_value_id'            => ['name' => 'task_attr_value_id',       'type' => 'int',      'internal' => 'id'],
        'task_attr_value_default'       => ['name' => 'task_attr_value_default',  'type' => 'bool',     'internal' => 'isDefault'],
        'task_attr_value_valueStr'      => ['name' => 'task_attr_value_valueStr', 'type' => 'string',   'internal' => 'valueStr'],
        'task_attr_value_valueInt'      => ['name' => 'task_attr_value_valueInt', 'type' => 'int',      'internal' => 'valueInt'],
        'task_attr_value_valueDec'      => ['name' => 'task_attr_value_valueDec', 'type' => 'float',    'internal' => 'valueDec'],
        'task_attr_value_valueDat'      => ['name' => 'task_attr_value_valueDat', 'type' => 'DateTime', 'internal' => 'valueDat'],
        'task_attr_value_unit'          => ['name' => 'task_attr_value_unit', 'type' => 'string', 'internal' => 'unit'],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'l11n' => [
            'mapper'   => TaskAttributeValueL11nMapper::class,
            'table'    => 'task_attr_value_l11n',
            'self'     => 'task_attr_value_l11n_value',
            'column'   => 'content',
            'external' => null,
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'task_attr_value';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'task_attr_value_id';
}
