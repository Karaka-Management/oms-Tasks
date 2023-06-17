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

use Modules\Dashboard\Models\DashboardElementInterface;
use Modules\Media\Models\MediaMapper;
use Modules\Profile\Models\SettingsEnum;
use Modules\Tasks\Models\AccountRelationMapper;
use Modules\Tasks\Models\PermissionCategory;
use Modules\Tasks\Models\TaskElementMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskStatus;
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
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface Returns a renderable object
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewTaskDashboard(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        /** @var \phpOMS\Model\Html\Head $head */
        $head = $response->data['Content']->head;
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css?v=1.0.0');

        $view->setTemplate('/Modules/Tasks/Theme/Backend/task-dashboard');
        $view->data['nav'] = $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response);

        $mapperQuery = TaskMapper::getAnyRelatedToUser($request->header->account)
            ->with('tags')
            ->with('tags/title')
            ->where('status', TaskStatus::OPEN, '!=')
            ->where('tags/title/language', $response->header->l11n->language)
            ->sort('createdAt', OrderType::DESC)
            ->limit(25);

        if ($request->getData('ptype') === 'p') {
            $view->data['tasks'] = $mapperQuery->with('createdBy')
                    ->where('id', $request->getDataInt('id') ?? 0, '<')
                    ->execute();
        } elseif ($request->getData('ptype') === 'n') {
            $view->data['tasks'] = $mapperQuery->with('createdBy')
                    ->where('id', $request->getDataInt('id') ?? 0, '>')
                    ->execute();
        } else {
            $view->data['tasks'] = $mapperQuery->with('createdBy')
                    ->where('id', 0, '>')
                    ->execute();
        }

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
            ->where('status', TaskStatus::OPEN)
            ->sort('createdAt', OrderType::DESC)
            ->query($openQuery)
            ->execute();

        $view->data['open'] = $open;

        return $view;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function viewDashboard(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
    {
        /** @var \phpOMS\Model\Html\Head $head */
        $head = $response->data['Content']->head;
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css?v=1.0.0');

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
            ->where('tags/title/language', $response->header->l11n->language)
            ->execute();

        $view->data['tasks'] = $tasks;

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface Returns a renderable object
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewTaskView(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
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
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css?v=1.0.0');

        /** @var \Modules\Tasks\Models\Task $task */
        $task = TaskMapper::get()
            ->with('createdBy')
            ->with('media')
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

        $view->setTemplate('/Modules/Tasks/Theme/Backend/task-single');
        $view->data['task'] = $task;
        $view->data['nav']  = $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response);

        $accGrpSelector               = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->data['accGrpSelector'] = $accGrpSelector;

        $editor               = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->data['editor'] = $editor;

        return $view;
    }

    /**
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface Returns a renderable object
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewTaskCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
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
     * Routing end-point for application behaviour.
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return RenderableInterface Returns a renderable object
     *
     * @since 1.0.0
     * @codeCoverageIgnore
     */
    public function viewTaskAnalysis(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : RenderableInterface
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
        return TaskMapper::countUnread($account);
    }
}
