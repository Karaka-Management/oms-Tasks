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
 */
final class TaskAttributeMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'task_attr_id'    => ['name' => 'task_attr_id',    'type' => 'int', 'internal' => 'id'],
        'task_attr_task'  => ['name' => 'task_attr_task',  'type' => 'int', 'internal' => 'task'],
        'task_attr_type'  => ['name' => 'task_attr_type',  'type' => 'int', 'internal' => 'type'],
        'task_attr_value' => ['name' => 'task_attr_value', 'type' => 'int', 'internal' => 'value'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'type' => [
            'mapper'   => TaskAttributeTypeMapper::class,
            'external' => 'task_attr_type',
        ],
        'value' => [
            'mapper'   => TaskAttributeValueMapper::class,
            'external' => 'task_attr_value',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'task_attr';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'task_attr_id';
}
