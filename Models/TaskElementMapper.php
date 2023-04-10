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

use Modules\Admin\Models\AccountMapper;
use Modules\Media\Models\MediaMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;

/**
 * Mapper class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of TaskElement
 * @extends DataMapperFactory<T>
 */
final class TaskElementMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'task_element_id'         => ['name' => 'task_element_id',         'type' => 'int',      'internal' => 'id'],
        'task_element_desc'       => ['name' => 'task_element_desc',       'type' => 'string',   'internal' => 'description'],
        'task_element_desc_raw'   => ['name' => 'task_element_desc_raw',   'type' => 'string',   'internal' => 'descriptionRaw'],
        'task_element_status'     => ['name' => 'task_element_status',     'type' => 'int',      'internal' => 'status'],
        'task_element_priority'   => ['name' => 'task_element_priority',   'type' => 'int',      'internal' => 'priority'],
        'task_element_due'        => ['name' => 'task_element_due',        'type' => 'DateTime', 'internal' => 'due'],
        'task_element_task'       => ['name' => 'task_element_task',       'type' => 'int',      'internal' => 'task'],
        'task_element_created_by' => ['name' => 'task_element_created_by', 'type' => 'int',      'internal' => 'createdBy', 'readonly' => true],
        'task_element_created_at' => ['name' => 'task_element_created_at', 'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'media' => [
            'mapper'   => MediaMapper::class,
            'table'    => 'task_element_media',
            'external' => 'task_element_media_dst',
            'self'     => 'task_element_media_src',
        ],
        'accRelation'          => [
            'mapper'       => AccountRelationMapper::class,
            'table'        => 'task_account',
            'self'         => 'task_account_task_element',
            'external'     => null,
        ],
        'grpRelation'          => [
            'mapper'       => GroupRelationMapper::class,
            'table'        => 'task_group',
            'self'         => 'task_group_task_element',
            'external'     => null,
        ],
    ];

    /**
     * Belongs to.
     *
     * @var array<string, array{mapper:class-string, external:string, column?:string, by?:string}>
     * @since 1.0.0
     */
    public const BELONGS_TO = [
        'createdBy' => [
            'mapper'     => AccountMapper::class,
            'external'   => 'task_element_created_by',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'task_element';

    /**
     * Created at.
     *
     * @var string
     * @since 1.0.0
     */
    public const CREATED_AT = 'task_element_created_at';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'task_element_id';
}
