<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Controller;

use Modules\Tasks\Models\TaskMapper;
use phpOMS\DataStorage\Database\Query\OrderType;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\System\MimeType;

/**
 * Search class.
 *
 * @package Modules\Tasks
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class SearchController extends Controller
{
    /**
     * Api method to search for tags
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function searchGeneral(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        // @performance Guaranteed <= 1 hasMany selects should behave like a join instead of creating sub-queries
        //      https://github.com/Karaka-Management/phpOMS/issues/363

        // @bug limit(1, 'taskElements') applies to all taskElements not just taskElements per task!
        //      https://github.com/Karaka-Management/phpOMS/issues/362

        /** @var \Modules\Tasks\Models\Task[] $tasks */
        $tasks = TaskMapper::getAll()
            ->with('tags')
            ->with('tags/title')
            ->with('taskElements')
            ->where('title', '%' . ($request->getDataString('search') ?? '') . '%', 'LIKE')
            ->where('tags/title/language', $response->header->l11n->language)
            ->sort('createdAt', OrderType::DESC)
            ->sort('taskElements/createdAt', OrderType::ASC)
            ->limit(25)
            //->limit(1, 'taskElements')
            ->execute();

        $results = [];
        $count = 0;

        foreach ($tasks as $task) {
            if ($count >= 8) {
                break;
            }

            // @performance Check if this can be combined with the above getAll()
            //      https://github.com/Karaka-Management/oms-Tasks/issues/41
            if (!TaskMapper::hasReadingPermission($request->header->account, $task->id)) {
                continue;
            }

            ++$count;

            $results[] = [
                'title'     => $task->title,
                'summary'   => \substr(\trim($task->description), 0, 500),
                'link'      => '{/base}/task/view?id=' . $task->id,
                'account'   => '',
                'createdAt' => $task->createdAt,
                'image' => '',
                'tags'  => $task->tags,
                'type'  => 'list_links',
                'module'  => 'Tasks',
            ];
        }

        $response->header->set('Content-Type', MimeType::M_JSON . '; charset=utf-8', true);
        $response->add($request->uri->__toString(), $results);
    }
}
