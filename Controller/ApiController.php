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

use Modules\Admin\Models\AccountMapper;
use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\MediaMapper;
use Modules\Media\Models\NullMedia;
use Modules\Media\Models\PathSettings;
use Modules\Media\Models\Reference;
use Modules\Media\Models\ReferenceMapper;
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
 * @license OMS License 2.0
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
        /** @var AccountRelation[] $responsible */
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

        if (!empty($request->files)
            || !empty($request->getDataJson('media'))
        ) {
            $this->createTaskMedia($task, $request);
        }

        $this->createStandardCreateResponse($request, $response, $task);
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
        $account = AccountMapper::get()->where('id', $request->header->account)->execute();

        $collection = null;

        if (!empty($uploadedFiles = $request->files)) {
            $uploaded = $this->app->moduleManager->get('Media', 'Api')->uploadFiles(
                names: [],
                fileNames: [],
                files: $uploadedFiles,
                account: $request->header->account,
                basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
                virtualPath: $path,
                pathSettings: PathSettings::FILE_PATH
            );

            foreach ($uploaded as $media) {
                $this->createModelRelation(
                    $request->header->account,
                    $task->id,
                    $media->id,
                    TaskMapper::class,
                    'files',
                    '',
                    $request->getOrigin()
                );

                $accountPath = '/Accounts/'
                    . $account->id . ' '
                    . $account->login . '/Tasks/'
                    . $task->createdAt->format('Y') . '/'
                    . $task->createdAt->format('m') . '/'
                    . $task->id;

                $ref            = new Reference();
                $ref->name      = $media->name;
                $ref->source    = new NullMedia($media->id);
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($accountPath);

                $this->createModel($request->header->account, $ref, ReferenceMapper::class, 'media_reference', $request->getOrigin());

                if ($collection === null) {
                    /** @var \Modules\Media\Models\Collection $collection */
                    $collection = MediaMapper::getParentCollection($path)->limit(1)->execute();

                    if ($collection->id === 0) {
                        $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                            $accountPath,
                            $request->header->account,
                            __DIR__ . '/../../../Modules/Media/Files/Accounts/'
                                . $account->id . '/Tasks/'
                                . $task->createdAt->format('Y') . '/'
                                . $task->createdAt->format('m') . '/'
                                . $task->id
                        );
                    }
                }

                $this->createModelRelation(
                    $request->header->account,
                    $collection->id,
                    $ref->id,
                    CollectionMapper::class,
                    'sources',
                    '',
                    $request->getOrigin()
                );
            }
        }

        $mediaFiles = $request->getDataJson('media');
        foreach ($mediaFiles as $file) {
            /** @var \Modules\Media\Models\Media $media */
            $media = MediaMapper::get()->where('id', (int) $file)->limit(1)->execute();

            $this->createModelRelation(
                $request->header->account,
                $task->id,
                $media->id,
                TaskMapper::class,
                'files',
                '',
                $request->getOrigin()
            );

            $ref            = new Reference();
            $ref->name      = $media->name;
            $ref->source    = new NullMedia($media->id);
            $ref->createdBy = new NullAccount($request->header->account);
            $ref->setVirtualPath($path);

            $this->createModel($request->header->account, $ref, ReferenceMapper::class, 'media_reference', $request->getOrigin());

            if ($collection === null) {
                /** @var \Modules\Media\Models\Collection $collection */
                $collection = MediaMapper::getParentCollection($path)->limit(1)->execute();

                if ($collection->id === 0) {
                    $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                        $path,
                        $request->header->account,
                        __DIR__ . '/../../../Modules/Media/Files' . $path
                    );
                }
            }

            $this->createModelRelation(
                $request->header->account,
                $collection->id,
                $ref->id,
                CollectionMapper::class,
                'sources',
                '',
                $request->getOrigin()
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

        if ($request->hasData('due')) {
            $task->due = $request->getDataDateTime('due');
        } else {
            $task->priority = (int) $request->getData('priority');
        }

        if ($request->hasData('tags')) {
            $task->tags = $this->app->moduleManager->get('Tag', 'Api')->createTagsFromRequest($request);
        }

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
        $account = AccountMapper::get()->where('id', $request->header->account)->execute();

        $collection = null;

        if (!empty($uploadedFiles = $request->files)) {
            $uploaded = $this->app->moduleManager->get('Media', 'Api')->uploadFiles(
                [],
                [],
                $uploadedFiles,
                $request->header->account,
                __DIR__ . '/../../../Modules/Media/Files' . $path,
                $path,
            );

            foreach ($uploaded as $media) {
                $this->createModelRelation(
                    $request->header->account,
                    $element->id,
                    $media->id,
                    TaskElementMapper::class,
                    'files',
                    '',
                    $request->getOrigin()
                );

                $accountPath = '/Accounts/' . $account->id . ' '
                    . $account->login . '/Tasks/'
                    . $task->createdAt->format('Y') . '/'
                    . $task->createdAt->format('m') . '/'
                    . $task->id;

                $ref            = new Reference();
                $ref->name      = $media->name;
                $ref->source    = new NullMedia($media->id);
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($accountPath);

                $this->createModel($request->header->account, $ref, ReferenceMapper::class, 'media_reference', $request->getOrigin());

                $collection ??= $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                    $accountPath,
                    $request->header->account,
                    __DIR__ . '/../../../Modules/Media/Files/Accounts/' . $account->id
                        . '/Tasks/' . $task->createdAt->format('Y') . '/'
                        . $task->createdAt->format('m') . '/'
                        . $task->id
                );

                $this->createModelRelation(
                    $request->header->account,
                    $collection->id,
                    $ref->id,
                    CollectionMapper::class,
                    'sources',
                    '',
                    $request->getOrigin()
                );
            }
        }

        $mediaFiles = $request->getDataJson('media');
        foreach ($mediaFiles as $file) {
            /** @var \Modules\Media\Models\Media $media */
            $media = MediaMapper::get()->where('id', (int) $file)->limit(1)->execute();

            $this->createModelRelation(
                $request->header->account,
                $element->id,
                $media->id,
                TaskElementMapper::class,
                'files',
                '',
                $request->getOrigin()
            );

            $ref            = new Reference();
            $ref->name      = $media->name;
            $ref->source    = new NullMedia($media->id);
            $ref->createdBy = new NullAccount($request->header->account);
            $ref->setVirtualPath($path);

            $this->createModel($request->header->account, $ref, ReferenceMapper::class, 'media_reference', $request->getOrigin());

            $collection ??= $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                $path,
                $request->header->account,
                __DIR__ . '/../../../Modules/Media/Files' . $path
            );

            $this->createModelRelation(
                $request->header->account,
                $collection->id,
                $ref->id,
                CollectionMapper::class,
                'sources',
                '',
                $request->getOrigin()
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
