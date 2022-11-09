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
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Models;

use Modules\Admin\Models\AccountMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Task seen mapper class.
 *
 * This class is used to mark a task as seen. Additionally, you may set a reminder flag which can be used to highlight
 * a task.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 1.0
 * @link    https://karaka.app
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
        'task_seen_id'            => ['name' => 'task_seen_id',   'type' => 'int',      'internal' => 'id'],
        'task_seen_at'            => ['name' => 'task_seen_at',   'type' => 'DateTime', 'internal' => 'seenAt'],
        'task_seen_task'          => ['name' => 'task_seen_task', 'type' => 'int',      'internal' => 'task'],
        'task_seen_by'            => ['name' => 'task_seen_by',   'type' => 'int',      'internal' => 'seenBy'],
        'task_seen_reminder'      => ['name' => 'task_seen_reminder', 'type' => 'bool',      'internal' => 'isRemindered'],
        'task_seen_reminder_at'   => ['name' => 'task_seen_reminder_at',   'type' => 'DateTime', 'internal' => 'reminderAt'],
        'task_seen_reminder_by'   => ['name' => 'task_seen_reminder_by',   'type' => 'int',      'internal' => 'reminderBy'],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'reminderBy' => [
            'mapper'     => AccountMapper::class,
            'external'   => 'task_seen_reminder_by',
        ],
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
