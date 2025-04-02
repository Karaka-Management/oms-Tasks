<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Controller;

use Modules\Admin\Models\AccountMapper;
use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\Media;
use Modules\Media\Models\PathSettings;
use Modules\Notification\Models\Notification;
use Modules\Notification\Models\NotificationMapper;
use Modules\Notification\Models\NotificationType;
use Modules\Profile\Models\ProfileMapper;
use Modules\Tasks\Models\PermissionCategory;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskElement;
use Modules\Tasks\Models\TaskElementMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskPriority;
use Modules\Tasks\Models\TaskSeen;
use Modules\Tasks\Models\TaskSeenMapper;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Utils\Parser\Markdown\Markdown;

/**
 * Api controller for the tasks module.
 *
 * @package Modules\Tasks
 * @license OMS License 2.2
 * @link    https://jingga.app
 * @since   1.0.0
 */
final class ApiController extends Controller
{
    /**
     * Api method to remind a task
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
    public function apiTaskReminderCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateTaskReminderCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        /** @var TaskSeen[] $reminder */
        $reminder = $this->createTaskReminderFromRequest($request);
        $this->createModel($request->header->account, $reminder, TaskSeenMapper::class, 'reminder', $request->getOrigin());
        $this->createStandardCreateResponse($request, $response, $reminder);
    }

    /**
     * Validate reminder create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool> Returns the validation array of the request
     *
     * @since 1.0.0
     */
    private function validateTaskReminderCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['id'] = !$request->hasData('id'))) {
            return $val;
        }

        return [];
    }

    /**
     * Validate task create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool> Returns the validation array of the request
     *
     * @since 1.0.0
     */
    private function validateTaskCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['plain'] = !$request->hasData('plain'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Method to create reminder from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return TaskSeen[] Returns the created reminders from the request
     *
     * @since 1.0.0
     */
    public function createTaskReminderFromRequest(RequestAbstract $request) : array
    {
        /** @var \Modules\Tasks\Models\AccountRelation[] $responsible */
        $responsible = TaskMapper::getResponsible((int) $request->getData('id'));

        $reminder = [];
        foreach ($responsible as $account) {
            $unseen             = new TaskSeen();
            $unseen->task       = (int) $request->getData('id');
            $unseen->seenBy     = $account->relation->id;
            $unseen->reminderBy = new NullAccount($request->header->account);
            $unseen->reminderAt = new \DateTime('now');

            $reminder[] = $unseen;
        }

        return $reminder;
    }

    /**
     * Api method to create a task
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
    public function apiTaskCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateTaskCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        /** @var Task $task */
        $task = $this->createTaskFromRequest($request);
        $this->createModel($request->header->account, $task, TaskMapper::class, 'task', $request->getOrigin());

        $first = \reset($task->taskElements);
        if ($first !== false) {
            $this->createNotifications($first, NotificationType::CREATE, $request);
        }

        if (!empty($request->files)
            || !empty($request->getDataJson('media'))
        ) {
            $this->createTaskMedia($task, $request);
        }

        $this->createStandardCreateResponse($request, $response, $task);
    }

    /**
     * Create notifications for users.
     *
     * This is usually called when creating a task or adding a new child element.
     *
     * @param TaskElement     $ele     Task element
     * @param int             $type    Notification type (e.g. new, new child element, ...)
     * @param RequestAbstract $request Request that caused this notification to get created
     *
     * @return void
     *
     * @performance This should happen in the cli if possible?
     *
     * @since 1.0.0
     */
    public function createNotifications(TaskElement $ele, int $type, RequestAbstract $request) : void
    {
        $accChecked = [$ele->createdBy->id];
        //$grpChecked = [];

        $task = TaskMapper::get()
            ->with('taskElements')
            ->with('taskElements/accRelation')
            //->with('taskElements/grpRelation')
            ->where('id', $ele->task)
            ->execute();

        // Don't notify on template generations
        if ($task->type !== TaskType::SINGLE) {
            return;
        }

        // We have to check all previous elements as well because other accounts/groups are probably defined in a
        // previous task element.
        foreach ($task->taskElements as $element) {
            // Account relations
            foreach ($element->accRelation as $rel) {
                if (\in_array($rel->relation->id, $accChecked)) {
                    continue;
                }

                $accChecked[] = $rel->relation->id;

                $profile = ProfileMapper::count()
                    ->where('account', $rel->relation->id)
                    ->executeCount();

                if ($profile < 1) {
                    continue;
                }

                $notification             = new Notification();
                $notification->module     = self::NAME;
                $notification->title      = $task->title;
                $notification->createdBy  = $element->createdBy;
                $notification->createdFor = $rel->relation;
                $notification->type       = $type;
                $notification->category   = PermissionCategory::TASK;
                $notification->element    = $task->id;
                $notification->redirect   = empty($task->redirect)
                    ? '{/base}/task/view?{?}&id=' . $element->task
                    : $task->redirect;

                $this->createModel($request->header->account, $notification, NotificationMapper::class, 'notification', $request->getOrigin());
            }

            // Group relations
            /* Ignore groups since they are not directly mentioned
            foreach ($element->grpRelation as $rel) {
                if (\in_array($rel->relation->id, $grpChecked)) {
                    continue;
                }

                $group = GroupMapper::get()
                    ->with('accounts')
                    ->where('id', $rel->relation->id)
                    ->execute();

                foreach ($group->accounts as $account) {
                    if (\in_array($account->id, $accChecked)) {
                        continue;
                    }

                    $profile = ProfileMapper::count()
                        ->where('account', $account->id)
                        ->executeCount();

                    if ($profile < 1) {
                        continue;
                    }

                    $notification             = new Notification();
                    $notification->module     = self::NAME;
                    $notification->title      = $task->title;
                    $notification->createdBy  = $element->createdBy;
                    $notification->createdFor = $account;
                    $notification->type       = $type;
                    $notification->category   = PermissionCategory::TASK;
                    $notification->element    = $task->id;
                    $notification->redirect   = empty($task->redirect)
                        ? '{/base}/task/view?{?}&id=' . $element->task
                        : $task->redirect;

                    $this->createModel($request->header->account, $notification, NotificationMapper::class, 'notification', $request->getOrigin());
                    $accChecked[] = $account->id;
                }

                $grpChecked[] = $rel->relation->id;
            }
            */
        }
    }

    /**
     * Create media files for task
     *
     * @param Task            $task    Task
     * @param RequestAbstract $request Request incl. media do upload
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function createTaskMedia(Task $task, RequestAbstract $request) : void
    {
        $path = $this->createTaskDir($task);

        /** @var \Modules\Admin\Models\Account $account */
        $account     = AccountMapper::get()->where('id', $request->header->account)->execute();
        $accountPath = '/Accounts/'
            . $account->id . ' '
            . $account->login . '/Tasks/'
            . $task->createdAt->format('Y') . '/'
            . $task->createdAt->format('m') . '/'
            . $task->id;

        if (!empty($request->files)) {
            $uploaded = $this->app->moduleManager->get('Media', 'Api')->uploadFiles(
                names: [],
                fileNames: [],
                files: $request->files,
                account: $request->header->account,
                basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
                virtualPath: $path,
                pathSettings: PathSettings::FILE_PATH,
                rel: $task->id,
                mapper: TaskMapper::class,
                field: 'files'
            );

            if ($account->id !== 0) {
                $this->app->moduleManager->get('Media', 'Api')->addMediaToCollectionAndModel(
                    account: $request->header->account,
                    files: \array_map(function (Media $media) { return $media->id; }, $uploaded->sources),
                    collectionPath: $accountPath
                );
            }
        }

        if (!empty($media = $request->getDataJson('media'))) {
            $this->app->moduleManager->get('Media', 'Api')->addMediaToCollectionAndModel(
                $request->header->account,
                $media,
                $task->id,
                TaskMapper::class,
                'files',
                $path
            );
        }
    }

    /**
     * Create media directory path
     *
     * @param Task $task Task
     *
     * @return string
     *
     * @since 1.0.0
     */
    private function createTaskDir(Task $task) : string
    {
        return '/Modules/Tasks/'
            . $task->createdAt->format('Y') . '/'
            . $task->createdAt->format('m') . '/'
            . $task->createdAt->format('d') . '/'
            . $task->id;
    }

    /**
     * Method to create task from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return Task Returns the created task from the request
     *
     * @since 1.0.0
     */
    public function createTaskFromRequest(RequestAbstract $request) : Task
    {
        $task                 = new Task();
        $task->title          = $request->getDataString('title') ?? '';
        $task->description    = Markdown::parse($request->getDataString('plain') ?? '');
        $task->descriptionRaw = $request->getDataString('plain') ?? '';
        $task->createdBy      = new NullAccount($request->header->account);
        $task->for            = $request->hasData('for') ? new NullAccount((int) $request->getData('for')) : null;
        $task->status         = TaskStatus::OPEN;
        $task->type           = TaskType::tryFromValue($request->getDataInt('type')) ?? TaskType::SINGLE;
        $task->redirect       = $request->getDataString('redirect') ?? '';
        $task->unit           = $request->getDataInt('unit');

        if ($request->hasData('due')) {
            $task->due = $request->getDataDateTime('due');
        } else {
            $task->priority = (int) $request->getData('priority');
        }

        /*
        if ($request->hasData('tags')) {
            $task->tags = $this->app->moduleManager->get('Tag', 'Api')->createTagsFromRequest($request);
        }
        */

        $element = new TaskElement();
        $element->addTo(new NullAccount($request->getDataInt('forward') ?? $request->header->account));
        $element->createdBy = $task->createdBy;
        $element->due       = $task->due;
        $element->priority  = $task->priority;
        $element->status    = TaskStatus::OPEN;

        $task->addElement($element);

        return $task;
    }

    /**
     * Api method to get a task
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
    public function apiTaskGet(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var Task $task */
        $task = TaskMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->createStandardReturnResponse($request, $response, $task);
    }

    /**
     * Api method to update a task
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
    public function apiTaskSet(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var Task $old */
        $old = TaskMapper::get()->where('id', (int) $request->getData('id'))->execute();

        /** @var Task $new */
        $new = $this->updateTaskFromRequest($request, clone $old);
        $this->updateModel($request->header->account, $old, $new, TaskMapper::class, 'task', $request->getOrigin());

        if (!empty($new->trigger)) {
            $this->app->eventManager->triggerSimilar($new->trigger, '', $new);
        }

        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update an task from a request
     *
     * @param RequestAbstract $request Request
     *
     * @return Task Returns the updated task from the request
     *
     * @since 1.0.0
     */
    private function updateTaskFromRequest(RequestAbstract $request, Task $task) : Task
    {
        $task->title          = $request->getDataString('title') ?? $task->title;
        $task->description    = Markdown::parse($request->getDataString('plain') ?? $task->descriptionRaw);
        $task->descriptionRaw = $request->getDataString('plain') ?? $task->descriptionRaw;
        $task->due            = $request->hasData('due') ? new \DateTime($request->getDataString('due') ?? 'now') : $task->due;
        $task->status         = TaskStatus::tryFromValue($request->getDataInt('status')) ?? $task->status;
        $task->type           = TaskType::tryFromValue($request->getDataInt('type')) ?? $task->type;
        $task->priority       = TaskPriority::tryFromValue($request->getDataInt('priority')) ?? $task->priority;
        $task->completion     = $request->getDataInt('completion') ?? $task->completion;

        return $task;
    }

    /**
     * Validate task element create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool> Returns the validation array of the request
     *
     * @since 1.0.0
     */
    private function validateTaskElementCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['status'] = !TaskStatus::isValidValue((int) $request->getData('status')))
            || ($val['due'] = !((bool) \strtotime((string) $request->getData('due'))))
            || ($val['task'] = !(\is_numeric($request->getData('task'))))
            || ($val['forward'] = !(\is_numeric($request->hasData('forward') ? $request->getData('forward') : $request->header->account)))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create a task element
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
    public function apiTaskElementCreate(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        if (!empty($val = $this->validateTaskElementCreate($request))) {
            $response->header->status = RequestStatusCode::R_400;
            $this->createInvalidCreateResponse($request, $response, $val);

            return;
        }

        /** @var \Modules\Tasks\Models\Task $task */
        $task    = TaskMapper::get()->where('id', (int) ($request->getData('task')))->execute();
        $element = $this->createTaskElementFromRequest($request, $task);

        $this->createNotifications($element, NotificationType::CHILDREN, $request);

        $task->due        = $element->due;
        $task->completion = $request->getDataInt('completion') ?? $task->completion;
        $task->priority   = $element->priority;
        $task->status     = $element->status;

        if ($task->status === TaskStatus::DONE) {
            $task->completion = 100;
        }

        $this->createModel($request->header->account, $element, TaskElementMapper::class, 'task_element', $request->getOrigin());

        if (!empty($request->files)
            || !empty($request->getDataJson('media'))
        ) {
            $this->createTaskElementMedia($task, $element, $request);
        }

        $this->updateModel($request->header->account, $task, $task, TaskMapper::class, 'task', $request->getOrigin());

        if (!empty($task->trigger)) {
            $this->app->eventManager->triggerSimilar($task->trigger, '', $task);
        }

        $this->createStandardCreateResponse($request, $response, $element);
    }

    /**
     * Create media files for task element
     *
     * @param Task            $task    Task
     * @param TaskElement     $element Task element
     * @param RequestAbstract $request Request incl. media do upload
     *
     * @return void
     *
     * @since 1.0.0
     */
    private function createTaskElementMedia(Task $task, TaskElement $element, RequestAbstract $request) : void
    {
        $path = $this->createTaskDir($task);

        /** @var \Modules\Admin\Models\Account $account */
        $account     = AccountMapper::get()->where('id', $request->header->account)->execute();
        $accountPath = '/Accounts/' . $account->id . ' '
            . $account->login . '/Tasks/'
            . $task->createdAt->format('Y') . '/'
            . $task->createdAt->format('m') . '/'
            . $task->id;

        if (!empty($request->files)) {
            $uploaded = $this->app->moduleManager->get('Media', 'Api')->uploadFiles(
                names: [],
                fileNames: [],
                files: $request->files,
                account: $request->header->account,
                basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
                virtualPath: $path,
                rel: $element->id,
                mapper: TaskElementMapper::class,
                field: 'files'
            );

            if ($account->id !== 0) {
                $this->app->moduleManager->get('Media', 'Api')->addMediaToCollectionAndModel(
                    account: $request->header->account,
                    files: \array_map(function (Media $media) { return $media->id; }, $uploaded->sources),
                    collectionPath: $accountPath
                );
            }
        }

        if (!empty($media = $request->getDataJson('media'))) {
            $this->app->moduleManager->get('Media', 'Api')->addMediaToCollectionAndModel(
                $request->header->account,
                $media,
                $element->id,
                TaskElementMapper::class,
                'files',
                $path
            );
        }
    }

    /**
     * Method to create task element from request.
     *
     * @param RequestAbstract $request Request
     * @param Task            $task    Task
     *
     * @return TaskElement Returns the task created from the request
     *
     * @since 1.0.0
     */
    public function createTaskElementFromRequest(RequestAbstract $request, Task $task) : TaskElement
    {
        $element                 = new TaskElement();
        $element->createdBy      = new NullAccount($request->header->account);
        $element->due            = $request->getDataDateTime('due') ?? $task->due;
        $element->priority       = TaskPriority::tryFromValue($request->getDataInt('priority')) ?? $task->priority;
        $element->status         = TaskStatus::tryFromValue($request->getDataInt('status')) ?? TaskStatus::OPEN;
        $element->task           = $task->id;
        $element->description    = Markdown::parse($request->getDataString('plain') ?? '');
        $element->descriptionRaw = $request->getDataString('plain') ?? '';
        $element->duration       = $request->getDataInt('duration') ?? 0;

        $tos = $request->getData('to') ?? $request->header->account;
        if (!\is_array($tos)) {
            $tos = [$tos];
        }

        $ccs = $request->getData('cc') ?? [];
        if (!\is_array($ccs)) {
            $ccs = [$ccs];
        }

        foreach ($tos as $to) {
            $element->addTo(new NullAccount((int) $to));
        }

        foreach ($ccs as $cc) {
            $element->addCC(new NullAccount((int) $cc));
        }

        return $element;
    }

    /**
     * Api method to get a task
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
    public function apiTaskElementGet(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var TaskElement $task */
        $task = TaskElementMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->createStandardReturnResponse($request, $response, $task);
    }

    /**
     * Api method to update a task element
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
    public function apiTaskElementSet(RequestAbstract $request, ResponseAbstract $response, array $data = []) : void
    {
        /** @var TaskElement $old */
        $old = TaskElementMapper::get()->where('id', (int) $request->getData('id'))->execute();

        /** @var TaskElement $new */
        $new = $this->updateTaskElementFromRequest($request, clone $old);
        $this->updateModel($request->header->account, $old, $new, TaskElementMapper::class, 'task_element', $request->getOrigin());

        if ($old->status !== $new->status
            || $old->priority !== $new->priority
            || $old->due !== $new->due
        ) {
            /** @var Task $task */
            $task = TaskMapper::get()->where('id', $new->task)->execute();

            $task->status   = $new->status;
            $task->priority = $new->priority;
            $task->due      = $new->due;

            $this->updateModel($request->header->account, $task, $task, TaskMapper::class, 'task', $request->getOrigin());

            if (!empty($task->trigger)) {
                $this->app->eventManager->triggerSimilar($task->trigger, '', $task);
            }
        }

        $this->createStandardUpdateResponse($request, $response, $new);
    }

    /**
     * Method to update an task element from a request
     *
     * @param RequestAbstract $request Request
     *
     * @return TaskElement Returns the updated task element from the request
     *
     * @since 1.0.0
     */
    private function updateTaskElementFromRequest(RequestAbstract $request, TaskElement $element) : TaskElement
    {
        $element->due            = $request->getDataDateTime('due') ?? $element->due;
        $element->status         = TaskStatus::tryFromValue($request->getDataInt('status')) ?? $element->status;
        $element->description    = Markdown::parse($request->getDataString('plain') ?? $element->descriptionRaw);
        $element->descriptionRaw = $request->getDataString('plain') ?? $element->descriptionRaw;

        $tos = $request->getData('to') ?? $request->header->account;
        if (!\is_array($tos)) {
            $tos = [$tos];
        }

        $ccs = $request->getData('cc') ?? [];
        if (!\is_array($ccs)) {
            $ccs = [$ccs];
        }

        foreach ($tos as $to) {
            $element->addTo($to);
        }

        foreach ($ccs as $cc) {
            $element->addCC($cc);
        }

        return $element;
    }
}
