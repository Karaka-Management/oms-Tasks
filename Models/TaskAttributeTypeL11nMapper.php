<?php
/**
 * Jingga
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
use phpOMS\Localization\BaseStringL11n;

/**
 * Task mapper class.
 *
 * @package Modules\Tasks\Models
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 *
 * @template T of BaseStringL11n
 * @extends DataMapperFactory<T>
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
        'task_attr_type_l11n_title' => ['name' => 'task_attr_type_l11n_title', 'type' => 'string', 'internal' => 'content', 'autocomplete' => true],
        'task_attr_type_l11n_type'  => ['name' => 'task_attr_type_l11n_type',  'type' => 'int',    'internal' => 'ref'],
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
    public const PRIMARYFIELD = 'task_attr_type_l11n_id';

    /**
     * Model to use by the mapper.
     *
     * @var class-string<T>
     * @since 1.0.0
     */
    public const MODEL = BaseStringL11n::class;
}
