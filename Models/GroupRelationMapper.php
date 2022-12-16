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

use Modules\Admin\Models\GroupMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Mapper class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 1.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class GroupRelationMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'task_group_id'           => ['name' => 'task_group_id',           'type' => 'int', 'internal' => 'id'],
        'task_group_duty'         => ['name' => 'task_group_duty',         'type' => 'int', 'internal' => 'duty'],
        'task_group_group'        => ['name' => 'task_group_group',        'type' => 'int', 'internal' => 'relation'],
        'task_group_task_element' => ['name' => 'task_group_task_element', 'type' => 'int', 'internal' => 'element'],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'relation' => [
            'mapper'     => GroupMapper::class,
            'external'   => 'task_group_group',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'task_group';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD ='task_group_id';
}
