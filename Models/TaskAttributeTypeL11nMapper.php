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
final class TaskAttributeTypeL11nMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'task_attr_type_l11n_id'    => ['name' => 'task_attr_type_l11n_id',    'type' => 'int',    'internal' => 'id'],
        'task_attr_type_l11n_title' => ['name' => 'task_attr_type_l11n_title', 'type' => 'string', 'internal' => 'title', 'autocomplete' => true],
        'task_attr_type_l11n_type'  => ['name' => 'task_attr_type_l11n_type',  'type' => 'int',    'internal' => 'type'],
        'task_attr_type_l11n_lang'  => ['name' => 'task_attr_type_l11n_lang',  'type' => 'string', 'internal' => 'language'],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'task_attr_type_l11n';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD ='task_attr_type_l11n_id';
}
