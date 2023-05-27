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
use Modules\Calendar\Models\ScheduleMapper;
use Modules\Media\Models\MediaMapper;
use Modules\Tag\Models\TagMapper;
use phpOMS\DataStorage\Database\Mapper\DataMapperFactory;
use phpOMS\DataStorage\Database\Mapper\ReadMapper;
use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\DataStorage\Database\Query\Where;

/**
 * Mapper class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of Task
 * @extends DataMapperFactory<T>
 */
final class TaskMapper extends DataMapperFactory
{
    /**
     * Columns.
     *
     * @var array<string, array{name:string, type:string, internal:string, autocomplete?:bool, readonly?:bool, writeonly?:bool, annotations?:array}>
     * @since 1.0.0
     */
    public const COLUMNS = [
        'task_id'                => ['name' => 'task_id',         'type' => 'int',      'internal' => 'id'],
        'task_title'             => ['name' => 'task_title',      'type' => 'string',   'internal' => 'title'],
        'task_desc'              => ['name' => 'task_desc',       'type' => 'string',   'internal' => 'description'],
        'task_desc_raw'          => ['name' => 'task_desc_raw',   'type' => 'string',   'internal' => 'descriptionRaw'],
        'task_type'              => ['name' => 'task_type',       'type' => 'int',      'internal' => 'type'],
        'task_status'            => ['name' => 'task_status',     'type' => 'int',      'internal' => 'status'],
        'task_completion'        => ['name' => 'task_completion',     'type' => 'int',      'internal' => 'completion'],
        'task_closable'          => ['name' => 'task_closable',   'type' => 'bool',     'internal' => 'isClosable'],
        'task_editable'          => ['name' => 'task_editable',   'type' => 'bool',     'internal' => 'isEditable'],
        'task_priority'          => ['name' => 'task_priority',   'type' => 'int',      'internal' => 'priority'],
        'task_due'               => ['name' => 'task_due',        'type' => 'DateTime', 'internal' => 'due'],
        'task_done'              => ['name' => 'task_done',       'type' => 'DateTime', 'internal' => 'done'],
        'task_schedule'          => ['name' => 'task_schedule',   'type' => 'int',      'internal' => 'schedule'],
        'task_start'             => ['name' => 'task_start',      'type' => 'DateTime', 'internal' => 'start'],
        'task_redirect'          => ['name' => 'task_redirect',      'type' => 'string',   'internal' => 'redirect'],
        'task_trigger'           => ['name' => 'task_trigger',      'type' => 'string',   'internal' => 'trigger'],
        'task_created_by'        => ['name' => 'task_created_by', 'type' => 'int',      'internal' => 'createdBy', 'readonly' => true],
        'task_created_at'        => ['name' => 'task_created_at', 'type' => 'DateTimeImmutable', 'internal' => 'createdAt', 'readonly' => true],
    ];

    /**
     * Has many relation.
     *
     * @var array<string, array{mapper:class-string, table:string, self?:?string, external?:?string, column?:string}>
     * @since 1.0.0
     */
    public const HAS_MANY = [
        'taskElements' => [
            'mapper'       => TaskElementMapper::class,
            'table'        => 'task_element',
            'self'         => 'task_element_task',
            'external'     => null,
        ],
        'media'        => [
            'mapper'   => MediaMapper::class,
            'table'    => 'task_media',
            'external' => 'task_media_dst',
            'self'     => 'task_media_src',
        ],
        'tags'         => [
            'mapper'   => TagMapper::class,
            'table'    => 'task_tag',
            'external' => 'task_tag_dst',
            'self'     => 'task_tag_src',
        ],
        'attributes' => [
            'mapper'   => TaskAttributeMapper::class,
            'table'    => 'task_attr',
            'self'     => 'task_attr_item',
            'external' => null,
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
            'external'   => 'task_created_by',
        ],
    ];

    /**
     * Has one relation.
     *
     * @var array<string, array{mapper:class-string, external:string, by?:string, column?:string, conditional?:bool}>
     * @since 1.0.0
     */
    public const OWNS_ONE = [
        'schedule' => [
            'mapper'     => ScheduleMapper::class,
            'external'   => 'task_schedule',
        ],
    ];

    /**
     * Primary table.
     *
     * @var string
     * @since 1.0.0
     */
    public const TABLE = 'task';

    /**
     * Created at.
     *
     * @var string
     * @since 1.0.0
     */
    public const CREATED_AT = 'task_created_at';

    /**
     * Primary field name.
     *
     * @var string
     * @since 1.0.0
     */
    public const PRIMARYFIELD = 'task_id';

    /**
     * Get open tasks by createdBy
     *
     * @param int $user User
     *
     * @return Task[]
     *
     * @since 1.0.0
     */
    public static function getOpenCreatedBy(int $user) : array
    {
        $query = self::getQuery();
        $query->where(self::TABLE . '_d1.task_created_by', '=', $user)
            ->where(self::TABLE . '_d1.task_status', '=', TaskStatus::OPEN);

        return self::getAll()->execute($query);
    }

    /**
     * Get open tasks for user
     *
     * @param int $user User
     *
     * @return Task[]
     *
     * @since 1.0.0
     */
    public static function getOpenTo(int $user) : array
    {
        $query = self::getQuery();
        $query->innerJoin(TaskElementMapper::TABLE)
                ->on(self::TABLE . '_d1.task_id', '=', TaskElementMapper::TABLE . '.task_element_task')
            ->innerJoin(AccountRelationMapper::TABLE)
                ->on(TaskElementMapper::TABLE . '.task_element_id', '=', AccountRelationMapper::TABLE . '.task_account_task_element')
            ->where(self::TABLE . '_d1.task_status', '=', TaskStatus::OPEN)
            ->andWhere(AccountRelationMapper::TABLE . '.task_account_account', '=', $user)
            ->andWhere(AccountRelationMapper::TABLE . '.task_account_duty', '=', DutyType::TO);

        return self::getAll()->execute($query);
    }

    /**
     * Get open tasks for mentioned user
     *
     * @param int $user User
     *
     * @return Task[]
     *
     * @since 1.0.0
     */
    public static function getOpenAny(int $user) : array
    {
        $query = self::getQuery();
        $query->innerJoin(TaskElementMapper::TABLE)
                ->on(self::TABLE . '_d1.task_id', '=', TaskElementMapper::TABLE . '.task_element_task')
            ->innerJoin(AccountRelationMapper::TABLE)
                ->on(TaskElementMapper::TABLE . '.task_element_id', '=', AccountRelationMapper::TABLE . '.task_account_task_element')
            ->where(self::TABLE . '_d1.task_status', '=', TaskStatus::OPEN)
            ->andWhere(AccountRelationMapper::TABLE . '.task_account_account', '=', $user);

        return self::getAll()->execute($query);
    }

    /**
     * Get open tasks by cc
     *
     * @param int $user User
     *
     * @return Task[]
     *
     * @since 1.0.0
     */
    public static function getOpenCC(int $user) : array
    {
        $query = self::getQuery();
        $query->innerJoin(TaskElementMapper::TABLE)
                ->on(self::TABLE . '_d1.task_id', '=', TaskElementMapper::TABLE . '.task_element_task')
            ->innerJoin(AccountRelationMapper::TABLE)
                ->on(TaskElementMapper::TABLE . '.task_element_id', '=', AccountRelationMapper::TABLE . '.task_account_task_element')
            ->where(self::TABLE . '_d1.task_status', '=', TaskStatus::OPEN)
            ->andWhere(AccountRelationMapper::TABLE . '.task_account_account', '=', $user)
            ->andWhere(AccountRelationMapper::TABLE . '.task_account_duty', '=', DutyType::CC);

        return self::getAll()->execute($query);
    }

    /**
     * Get tasks created by user
     *
     * @param int $user User
     *
     * @return Task[]
     *
     * @since 1.0.0
     */
    public static function getCreatedBy(int $user) : array
    {
        $query = self::getQuery();
        $query->where(self::TABLE . '_d1.task_created_by', '=', $user);

        return self::getAll()->execute($query);
    }

    /**
     * Get tasks sent to user
     *
     * @param int $user User
     *
     * @return Task[]
     *
     * @since 1.0.0
     */
    public static function getTo(int $user) : array
    {
        $query = self::getQuery();
        $query->innerJoin(TaskElementMapper::TABLE)
                ->on(self::TABLE . '_d1.task_id', '=', TaskElementMapper::TABLE . '.task_element_task')
            ->innerJoin(AccountRelationMapper::TABLE)
                ->on(TaskElementMapper::TABLE . '.task_element_id', '=', AccountRelationMapper::TABLE . '.task_account_task_element')
            ->where(AccountRelationMapper::TABLE . '.task_account_account', '=', $user)
            ->andWhere(AccountRelationMapper::TABLE . '.task_account_duty', '=', DutyType::TO);

        return self::getAll()->execute($query);
    }

    /**
     * Get tasks cc to user
     *
     * @param int $user User
     *
     * @return Task[]
     *
     * @since 1.0.0
     */
    public static function getCC(int $user) : array
    {
        $query = self::getQuery();
        $query->innerJoin(TaskElementMapper::TABLE)
                ->on(self::TABLE . '_d1.task_id', '=', TaskElementMapper::TABLE . '.task_element_task')
            ->innerJoin(AccountRelationMapper::TABLE)
                ->on(TaskElementMapper::TABLE . '.task_element_id', '=', AccountRelationMapper::TABLE . '.task_account_task_element')
            ->where(AccountRelationMapper::TABLE . '.task_account_account', '=', $user)
            ->andWhere(AccountRelationMapper::TABLE . '.task_account_duty', '=', DutyType::CC);

        return self::getAll()->execute($query);
    }

    /**
     * Get tasks that have something to do with the user
     *
     * @param int $user User
     *
     * @return ReadMapper
     *
     * @since 1.0.0
     */
    public static function getAnyRelatedToUser(int $user) : ReadMapper
    {
        $query = new Builder(self::$db, true);
        $query->innerJoin(TaskElementMapper::TABLE)
                ->on(self::TABLE . '_d1.task_id', '=', TaskElementMapper::TABLE . '.task_element_task')
            ->innerJoin(AccountRelationMapper::TABLE)
                ->on(TaskElementMapper::TABLE . '.task_element_id', '=', AccountRelationMapper::TABLE . '.task_account_task_element')
            ->where(AccountRelationMapper::TABLE . '.task_account_account', '=', $user)
            ->orWhere(self::TABLE . '_d1.task_created_by', '=', $user)
            ->groupBy(self::PRIMARYFIELD);

        return self::getAll()->query($query);
    }

    /**
     * Check if a user has reading permission for a task
     *
     * @param int $user User id
     * @param int $task Task id
     *
     * @return bool
     *
     * @since 1.0.0
     */
    public static function hasReadingPermission(int $user, int $task) : bool
    {
        $userWhere = new Where(self::$db);
        $userWhere->where(AccountRelationMapper::TABLE . '.task_account_account', '=', $user)
            ->orWhere(self::TABLE . '_d1.task_created_by', '=', $user);

        $query = new Builder(self::$db);
        $query->selectAs(self::TABLE . '_d1.' . self::PRIMARYFIELD, self::PRIMARYFIELD . '_d1')
            ->fromAs(self::TABLE, self::TABLE . '_d1')
            ->innerJoin(TaskElementMapper::TABLE)
                ->on(self::TABLE . '_d1.' . self::PRIMARYFIELD, '=', TaskElementMapper::TABLE . '.task_element_task')
            ->innerJoin(AccountRelationMapper::TABLE)
                ->on(TaskElementMapper::TABLE . '.' . TaskElementMapper::PRIMARYFIELD, '=', AccountRelationMapper::TABLE . '.task_account_task_element')
            ->where($userWhere)
            ->andWhere(self::TABLE . '_d1.' . self::PRIMARYFIELD, '=', $task);

        return !empty($query->execute()?->fetchAll());
    }

    /**
     * Count unread task
     *
     * @param int $user User
     *
     * @return int
     *
     * @since 1.0.0
     */
    public static function countUnread(int $user) : int
    {
        try {
            $query = new Builder(self::$db);

            $query->count('DISTINCT ' . self::TABLE . '.' . self::PRIMARYFIELD)
                ->from(self::TABLE)
                ->innerJoin(TaskElementMapper::TABLE)
                    ->on(self::TABLE . '.' . self::PRIMARYFIELD, '=', TaskElementMapper::TABLE . '.task_element_task')
                ->innerJoin(AccountRelationMapper::TABLE)
                    ->on(TaskElementMapper::TABLE . '.' . TaskElementMapper::PRIMARYFIELD, '=', AccountRelationMapper::TABLE . '.task_account_task_element')
                ->where(self::TABLE . '.task_status', '=', TaskStatus::OPEN)
                ->andWhere(AccountRelationMapper::TABLE . '.task_account_account', '=', $user);

            $sth = self::$db->con->prepare($query->toSql());
            $sth->execute();

            $fetched = $sth->fetchAll();

            if ($fetched === false) {
                return -1;
            }

            $count = $fetched[0][0] ?? 0;
        } catch (\Exception $_) {
            return -1;
        }

        return $count;
    }
}
