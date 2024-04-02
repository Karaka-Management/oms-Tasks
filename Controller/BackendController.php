<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Controller;

use Modules\Dashboard\Models\DashboardElementInterface;
use Modules\Media\Models\MediaMapper;
use Modules\Profile\Models\SettingsEnum;
use Modules\Tasks\Models\AccountRelationMapper;
use Modules\Tasks\Models\PermissionCategory;
use Modules\Tasks\Models\TaskElementMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskSeen;
use Modules\Tasks\Models\TaskSeenMapper;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
use Modules\Tasks\Views\TaskView;
use phpOMS\Account\PermissionType;
use phpOMS\Asset\AssetType;
use phpOMS\Contract\RenderableInterface;
use phpOMS\DataStorage\Database\Query\Builder;
use phpOMS\DataStorage\Database\Query\OrderType;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Views\View;

/**
 * Backend controller for the tasks module.
 *
 * @property \Web\WebApplication $app
 *
 * @package Modules\Tasks
 * @license OMS License 2.0
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class BackendController extends Controller implements DashboardElementInterface
{
    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface Returns a renderable object
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewTaskDashboard(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        /** @var \phpOMS\Model\Html\Head $head */
        $head = $response->data['Content']->head;
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css?v=' . self::VERSION);

        $view->setTemplate('/Modules/Tasks/Theme/Backend/task-dashboard');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response);

        $openQuery = new Builder($this->app->dbPool->get(), true);
        $openQuery->innerJoin(TaskElementMapper::TABLE)
                ->on(TaskMapper::TABLE . '_d1.' . TaskMapper::PRIMARYFIELD, '=', TaskElementMapper::TABLE . '.task_element_task')
            ->innerJoin(AccountRelationMapper::TABLE)
                ->on(TaskElementMapper::TABLE . '.' . TaskElementMapper::PRIMARYFIELD, '=', AccountRelationMapper::TABLE . '.task_account_task_element')
            ->andWhere(AccountRelationMapper::TABLE . '.task_account_account', '=', $request->header->account);

        /** @var \Modules\Tasks\Models\Task[] $open */
        $open = TaskMapper::getAll()
            ->with('createdBy')
            ->with('tags')
            ->with('tags/title')
            ->where('tags/title/language', $response->header->l11n->language)
            ->where('type', TaskType::SINGLE)
            ->where('status', TaskStatus::OPEN)
            ->sort('createdAt', OrderType::DESC)
            ->query($openQuery)
            ->executeGetArray();

        $view->data['open'] = $open;

        foreach ($view->data['open'] as $task) {
            $view->data['task_media'][$task->id] = TaskMapper::has()
                ->with('files')
                ->where('id', $task->id)
                ->execute();
        }

        // given
        // @todo this should also include forwarded tasks
        /** @var \Modules\Tasks\Models\Task[] $given */
        $given = TaskMapper::getAll()
            ->with('createdBy')
            ->with('tags')
            ->with('tags/title')
            ->with('taskElements')
            ->with('taskElements/accRelation')
            ->with('taskElements/accRelation/relation')
            ->where('tags/title/language', $response->header->l11n->language)
            ->where('type', TaskType::SINGLE)
            ->where('status', TaskStatus::OPEN)
            ->where('createdBy', $response->header->account, '=')
            ->sort('createdAt', OrderType::DESC)
            ->executeGetArray();

        $view->data['given'] = $given;

        foreach ($view->data['given'] as $task) {
            $view->data['task_media'][$task->id] = TaskMapper::has()
                ->with('files')
                ->where('id', $task->id)
                ->where('type', TaskType::SINGLE)
                ->execute();
        }

        $view->data['unread'] = TaskMapper::getUnread($request->header->account);

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface Returns a renderable object
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewTaskList(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $isModerator = false;
        if (!$this->app->accountManager->get($request->header->account)
            ->hasPermission(PermissionType::READ, $this->app->unitId, module: 'Tasks')
        ) {
            $isModerator = true;
        }

        $view = new View($this->app->l11nManager, $request, $response);

        /** @var \phpOMS\Model\Html\Head $head */
        $head = $response->data['Content']->head;
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css?v=' . self::VERSION);

        $view->setTemplate('/Modules/Tasks/Theme/Backend/task-list');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response);

        if ($isModerator) {
            $mapperQuery = TaskMapper::getAll()
                ->with('tags')
                ->with('tags/title')
                ->with('createdBy')
                ->where('status', TaskStatus::OPEN, '!=')
                ->where('type', TaskType::SINGLE)
                ->sort('createdAt', OrderType::DESC)
                ->limit(25);
        } else {
            $mapperQuery = TaskMapper::getAnyRelatedToUser($request->header->account)
                ->with('tags')
                ->with('tags/title')
                ->with('createdBy')
                ->where('status', TaskStatus::OPEN, '!=')
                ->where('type', TaskType::SINGLE)
                ->where('tags/title/language', $response->header->l11n->language)
                ->sort('createdAt', OrderType::DESC)
                ->limit(25);
        }

        if ($request->getData('ptype') === 'p') {
            $view->data['tasks'] = $mapperQuery->where('id', $request->getDataInt('offset') ?? 0, '<')
                ->executeGetArray();
        } elseif ($request->getData('ptype') === 'n') {
            $view->data['tasks'] = $mapperQuery->where('id', $request->getDataInt('offset') ?? 0, '>')
                ->executeGetArray();
        } else {
            $view->data['tasks'] = $mapperQuery->where('id', 0, '>')
                ->executeGetArray();
        }

        $view->data['task_media'] = [];

        /** @var \Modules\Tasks\Models\Task $task */
        foreach ($view->data['tasks'] as $task) {
            $view->data['task_media'][$task->id] = TaskMapper::has()
                ->with('files')
                ->where('id', $task->id)
                ->where('type', TaskType::SINGLE)
                ->execute();
        }

        $view->data['unread'] = TaskMapper::getUnread($request->header->account);

        return $view;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function viewDashboard(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        /** @var \phpOMS\Model\Html\Head $head */
        $head = $response->data['Content']->head;
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css?v=' . self::VERSION);

        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Tasks/Theme/Backend/dashboard-task');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response);

        /** @var \Modules\Tasks\Models\Task[] $tasks */
        $tasks = TaskMapper::getAnyRelatedToUser($request->header->account)
            ->with('tags')
            ->with('tags/title')
            ->sort('taskElements/createdAt', OrderType::DESC)
            ->limit(5)
            ->where('id', 0, '>')
            ->where('type', TaskType::SINGLE)
            ->where('tags/title/language', $response->header->l11n->language)
            ->execute();

        $view->data['tasks'] = $tasks;

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface Returns a renderable object
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewTaskView(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new TaskView($this->app->l11nManager, $request, $response);

        /** @var \Model\Setting $profileImage */
        $profileImage = $this->app->appSettings->get(names: SettingsEnum::DEFAULT_PROFILE_IMAGE, module: 'Profile');

        /** @var \Modules\Media\Models\Media $image */
        $image                     = MediaMapper::get()->where('id', (int) $profileImage->content)->execute();
        $view->defaultProfileImage = $image;

        if (!TaskMapper::hasReadingPermission($request->header->account, (int) $request->getData('id'))) {
            $response->header->status = RequestStatusCode::R_403;
            $view->setTemplate('/Web/Backend/Error/403');

            $this->app->loadLanguageFromPath(
                $response->header->l11n->language,
                __DIR__ . '/../../../Web/Backend/Error/lang/' . $response->header->l11n->language . '.lang.php'
            );

            return $view;
        }

        /** @var \phpOMS\Model\Html\Head $head */
        $head = $response->data['Content']->head;
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css?v=' . self::VERSION);

        /** @var \Modules\Tasks\Models\Task $task */
        $task = TaskMapper::get()
            ->with('createdBy')
            ->with('files')
            ->with('tags')
            ->with('tags/title')
            ->with('taskElements')
            ->with('taskElements/createdBy')
            ->with('taskElements/media')
            ->with('taskElements/accRelation')
            ->with('taskElements/accRelation/relation')
            ->where('id', (int) $request->getData('id'))
            ->where('tags/title/language', $request->header->l11n->language)
            ->execute();

        $accountId = $request->header->account;

        if (!($task->createdBy->id === $accountId
            || $task->isCCAccount($accountId)
            || $task->isToAccount($accountId))
            && !$this->app->accountManager->get($accountId)->hasPermission(
                PermissionType::READ, $this->app->unitId, $this->app->appId, self::NAME, PermissionCategory::TASK, $task->id)
        ) {
            $view->setTemplate('/Web/Backend/Error/403_inline');
            $response->header->status = RequestStatusCode::R_403;
            return $view;
        }

        $reminderStatus = [];

        // Set task as seen
        if ($task->id !== 0) {
            /** @var \Modules\Tasks\Models\TaskSeen[] $taskSeen */
            $taskSeen = TaskSeenMapper::getAll()
                ->with('reminderBy')
                ->where('task', $task->id)
                ->where('seenBy', $request->header->account)
                ->executeGetArray();

            foreach ($taskSeen as $unseen) {
                // Shows all reminders
                if ($unseen->reminderBy !== null
                    && ($unseen->reminderAt?->getTimestamp() ?? 0) < $request->header->getRequestTime()
                    && ($unseen->reminderAt?->getTimestamp() ?? 0) > $unseen->seenAt->getTimestamp() - 300
                ) {
                    $reminderStatus[] = $unseen;

                    if ($unseen->isRemindered) {
                        $new               = clone $unseen;
                        $new->seenAt       = new \DateTime('now');
                        $new->isRemindered = false;

                        $this->updateModel($request->header->account, $unseen, $new, TaskSeenMapper::class, 'reminder_seen', $request->getOrigin());
                    }
                }
            }

            if (empty($taskSeen)) {
                $taskSeen         = new TaskSeen();
                $taskSeen->seenBy = $request->header->account;
                $taskSeen->task   = (int) $request->getData('id');

                $this->createModel($request->header->account, $taskSeen, TaskSeenMapper::class, 'task_seen', $request->getOrigin());
            }
        }

        $view->data['reminder'] = $reminderStatus;

        $view->setTemplate('/Modules/Tasks/Theme/Backend/task-view');
        $view->data['task'] = $task;
        $view->data['nav']  = $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response);

        $accGrpSelector               = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['accGrpSelector'] = $accGrpSelector;

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface Returns a renderable object
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewTaskCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/Tasks/Theme/Backend/task-create');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response);

        $accGrpSelector               = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['accGrpSelector'] = $accGrpSelector;

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        return $view;
    }

    /**
     * Routing end-point for application behavior.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param array            $data     Generic data
     *
     * @return RenderableInterface Returns a renderable object
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewTaskAnalysis(RequestAbstract $request, ResponseAbstract $response, array $data = []) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Tasks/Theme/Backend/task-analysis');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response);

        return $view;
    }

    /**
     * Count unread messages
     *
     * @param int $account Account id
     *
     * @return int Returns the amount of unread tasks
     *
     * @since 1.0.0
     */
    public function openNav(int $account) : int
    {
        return \count(TaskMapper::getUnread($account));
    }
}
