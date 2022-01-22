<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Tasks\Models
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Tasks\Models;

use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Tasks mapper class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 1.0
 * @link    https://orange-management.org
 * @since   1.0.0
 */
final class TaskSeenMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'task_seen_id'   => ['name' => 'task_seen_id',   'type' => 'int',      'internal' => 'id'],
        'task_seen_at'   => ['name' => 'task_seen_at',   'type' => 'DateTime', 'internal' => 'seenAt'],
        'task_seen_task' => ['name' => 'task_seen_task', 'type' => 'int',      'internal' => 'task'],
        'task_seen_by'   => ['name' => 'task_seen_by',   'type' => 'int',      'internal' => 'seenBy'],
        'task_seen_flag' => ['name' => 'task_seen_flag', 'type' => 'bool',     'internal' => 'flag'],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'task_seen';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD ='task_seen_id';
}
