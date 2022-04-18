<?php
/**
 * Karaka
 *
 * PHP Version 8.0
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Controller;

use Modules\Dashboard\Models\DashboardElementInterface;
use Modules\Media\Models\MediaMapper;
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
 * @license OMS License 1.0
 * @link    https://karaka.app
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
    public function viewTaskDashboard(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        /** @var \phpOMS\Model\Html\Head $head */
        $head = $response->get('Content')->getData('head');
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css');

        $view->setTemplate('/Modules/Tasks/Theme/Backend/task-dashboard');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response));

        $mapperQuery = TaskMapper::getAnyRelatedToUser($request->header->account)
            ->with('tags')
            ->with('tags/title')
            ->where('status', TaskStatus::OPEN, '!=')
            ->where('tags/title/language', $response->getLanguage())
            ->sort('createdAt', OrderType::DESC)
            ->limit(25);

        if ($request->getData('ptype') === 'p') {
            $view->setData('tasks',
                $mapperQuery->with('createdBy')
                    ->where('id', (int) ($request->getData('id') ?? 0), '<')
                    ->execute()
            );
        } elseif ($request->getData('ptype') === 'n') {
            $view->setData('tasks',
                $mapperQuery->with('createdBy')
                    ->where('id', (int) ($request->getData('id') ?? 0), '>')
                    ->execute()
            );
        } else {
            $view->setData('tasks',
                $mapperQuery->with('createdBy')
                    ->where('id', 0, '>')
                    ->execute()
            );
        }

        $openQuery = new Builder($this->app->dbPool->get(), true);
        $openQuery->innerJoin(TaskElementMapper::TABLE)
                    ->on(TaskMapper::TABLE . '_d1.' . TaskMapper::PRIMARYFIELD, '=', TaskElementMapper::TABLE . '.task_element_task')
                ->innerJoin(AccountRelationMapper::TABLE)
                    ->on(TaskElementMapper::TABLE . '.' . TaskElementMapper::PRIMARYFIELD, '=', AccountRelationMapper::TABLE . '.task_account_task_element')
                ->andWhere(AccountRelationMapper::TABLE . '.task_account_account', '=', $request->header->account);

        $open = TaskMapper::getAll()
            ->with('createdBy')
            ->with('taskElements')
            ->with('tags')
            ->with('tags/title')
            ->where('tags/title/language', $response->getLanguage())
            ->where('status', TaskStatus::OPEN)
            ->sort('createdAt', OrderType::DESC)
            ->query($openQuery)
            ->execute();

        $view->setData('open', $open);

        return $view;
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnore
     */
    public function viewDashboard(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Tasks/Theme/Backend/dashboard-task');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response));

        $tasks = TaskMapper::getAnyRelatedToUser($request->header->account)
            ->with('tags')
            ->with('tags/title')
            ->sort('taskElements/createdAt', OrderType::DESC)
            ->limit(5)
            ->where('id', 0, '>')
            ->where('tags/title/language', $response->getLanguage())
            ->execute();

        $view->addData('tasks', $tasks);

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
    public function viewTaskView(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new TaskView($this->app->l11nManager, $request, $response);

        $profileImage              = $this->app->appSettings->get(names: 'default_profile_image', module: 'Profile');
        $image                     = MediaMapper::get()->where('id', (int) $profileImage->content)->execute();
        $view->defaultProfileImage = $image;

        if (!TaskMapper::hasReadingPermission($request->header->account, (int) $request->getData('id'))) {
            $response->header->status = RequestStatusCode::R_403;
            $view->setTemplate('/Web/Backend/Error/403');

            $this->app->loadLanguageFromPath(
                $response->getLanguage(),
                __DIR__ . '/../../../Web/Backend/Error/lang/' . $response->getLanguage() . '.lang.php'
            );

            return $view;
        }

        /** @var \phpOMS\Model\Html\Head $head */
        $head = $response->get('Content')->getData('head');
        $head->addAsset(AssetType::CSS, 'Modules/Tasks/Theme/Backend/css/styles.css');

        /** @var \Modules\Tasks\Models\Task $task */
        $task = TaskMapper::get()
            ->with('createdBy')
            ->with('taskElements')
            ->with('taskElements/createdBy')
            ->with('taskElements/media')
            ->with('taskElements/accRelation')
            ->with('taskElements/accRelation/relation')
            ->where('id', (int) $request->getData('id'))
            ->execute();

        $accountId = $request->header->account;

        if (!($task->createdBy->getId() === $accountId
            || $task->isCCAccount($accountId)
            || $task->isToAccount($accountId))
            && !$this->app->accountManager->get($accountId)->hasPermission(
                PermissionType::READ, $this->app->orgId, $this->app->appName, self::NAME, PermissionCategory::TASK, $task->getId())
        ) {
            $view->setTemplate('/Web/Backend/Error/403_inline');
            $response->header->status = RequestStatusCode::R_403;
            return $view;
        }

        $view->setTemplate('/Modules/Tasks/Theme/Backend/task-single');
        $view->addData('task', $task);
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response));

        $accGrpSelector = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('accGrpSelector', $accGrpSelector);

        $editor = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('editor', $editor);

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
    public function viewTaskCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);

        $view->setTemplate('/Modules/Tasks/Theme/Backend/task-create');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response));

        $accGrpSelector = new \Modules\Profile\Theme\Backend\Components\AccountGroupSelector\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('accGrpSelector', $accGrpSelector);

        $editor = new \Modules\Editor\Theme\Backend\Components\Editor\BaseView($this->app->l11nManager, $request, $response);
        $view->addData('editor', $editor);

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
    public function viewTaskAnalysis(RequestAbstract $request, ResponseAbstract $response, $data = null) : RenderableInterface
    {
        $view = new View($this->app->l11nManager, $request, $response);
        $view->setTemplate('/Modules/Tasks/Theme/Backend/task-analysis');
        $view->addData('nav', $this->app->moduleManager->get('Navigation')->createNavigationMid(1001101001, $request, $response));

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
