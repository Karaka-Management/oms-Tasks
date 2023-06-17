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
use Modules\Tag\Models\NullTag;
use Modules\Tasks\Models\NullTaskAttributeType;
use Modules\Tasks\Models\NullTaskAttributeValue;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskAttribute;
use Modules\Tasks\Models\TaskAttributeMapper;
use Modules\Tasks\Models\TaskAttributeType;
use Modules\Tasks\Models\TaskAttributeTypeL11nMapper;
use Modules\Tasks\Models\TaskAttributeTypeMapper;
use Modules\Tasks\Models\TaskAttributeValue;
use Modules\Tasks\Models\TaskAttributeValueL11nMapper;
use Modules\Tasks\Models\TaskAttributeValueMapper;
use Modules\Tasks\Models\TaskElement;
use Modules\Tasks\Models\TaskElementMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
use phpOMS\Localization\BaseStringL11n;
use phpOMS\Localization\ISO639x1Enum;
use phpOMS\Message\Http\HttpResponse;
use phpOMS\Message\Http\RequestStatusCode;
use phpOMS\Message\NotificationLevel;
use phpOMS\Message\RequestAbstract;
use phpOMS\Message\ResponseAbstract;
use phpOMS\Model\Message\FormValidation;
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
     * Api method to create a task
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaskCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTaskCreate($request))) {
            $response->data[$request->uri->__toString()] = new FormValidation($val);
            $response->header->status                    = RequestStatusCode::R_400;

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

        $this->fillJsonResponse(
            $request,
            $response,
            NotificationLevel::OK,
            '',
            $this->app->l11nManager->getText($response->header->l11n->language, '0', '0', 'SucessfulCreate'),
            $task
        );
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

        if (!empty($uploadedFiles = $request->files)) {
            $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
                names: [],
                fileNames: [],
                files: $uploadedFiles,
                account: $request->header->account,
                basePath: __DIR__ . '/../../../Modules/Media/Files' . $path,
                virtualPath: $path,
                pathSettings: PathSettings::FILE_PATH
            );

            $collection = null;
            foreach ($uploaded as $media) {
                $this->createModelRelation(
                    $request->header->account,
                    $task->id,
                    $media->id,
                    TaskMapper::class,
                    'media',
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

        if (!empty($mediaFiles = $request->getDataJson('media'))) {
            $collection = null;

            foreach ($mediaFiles as $file) {
                /** @var \Modules\Media\Models\Media $media */
                $media = MediaMapper::get()->where('id', (int) $file)->limit(1)->execute();

                $this->createModelRelation(
                    $request->header->account,
                    $task->id,
                    $media->id,
                    TaskMapper::class,
                    'media',
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
        $task->setCreatedBy(new NullAccount($request->header->account));
        $task->setStatus(TaskStatus::OPEN);
        $task->setType(TaskType::SINGLE);
        $task->redirect = $request->getDataString('redirect') ?? '';

        if (!$request->hasData('priority')) {
            $task->due = $request->getDataDateTime('due');
        } else {
            $task->setPriority((int) $request->getData('priority'));
        }

        if (!empty($tags = $request->getDataJson('tags'))) {
            foreach ($tags as $tag) {
                if (!isset($tag['id'])) {
                    $request->setData('title', $tag['title'], true);
                    $request->setData('color', $tag['color'], true);
                    $request->setData('icon', $tag['icon'] ?? null, true);
                    $request->setData('language', $tag['language'], true);

                    $internalResponse = new HttpResponse();
                    $this->app->moduleManager->get('Tag')->apiTagCreate($request, $internalResponse, null);

                    if (!\is_array($data = $internalResponse->get($request->uri->__toString()))) {
                        continue;
                    }

                    $task->addTag($data['response']);
                } else {
                    $task->addTag(new NullTag((int) $tag['id']));
                }
            }
        }

        $element = new TaskElement();
        $element->addTo(new NullAccount($request->getDataInt('forward') ?? $request->header->account));
        $element->createdBy = $task->getCreatedBy();
        $element->due       = $task->due;
        $element->setPriority($task->getPriority());
        $element->setStatus(TaskStatus::OPEN);

        $task->addElement($element);

        return $task;
    }

    /**
     * Api method to get a task
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaskGet(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        /** @var Task $task */
        $task = TaskMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Task', 'Task successfully returned.', $task);
    }

    /**
     * Api method to update a task
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaskSet(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        /** @var Task $old */
        $old = TaskMapper::get()->where('id', (int) $request->getData('id'))->execute();

        /** @var Task $new */
        $new = $this->updateTaskFromRequest($request, clone $old);
        $this->updateModel($request->header->account, $old, $new, TaskMapper::class, 'task', $request->getOrigin());

        if (!empty($new->trigger)) {
            $this->app->eventManager->triggerSimilar($new->trigger, '', $new);
        }

        $this->fillJsonResponse(
            $request,
            $response,
            NotificationLevel::OK,
            '',
            $this->app->l11nManager->getText($response->header->l11n->language, '0', '0', 'SucessfulUpdate'),
            $new
        );
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
        $task->title          = (string) ($request->getData('title') ?? $task->title);
        $task->description    = Markdown::parse((string) ($request->getData('plain') ?? $task->descriptionRaw));
        $task->descriptionRaw = (string) ($request->getData('plain') ?? $task->descriptionRaw);
        $task->due            = $request->hasData('due') ? new \DateTime((string) ($request->getData('due'))) : $task->due;
        $task->setStatus($request->getDataInt('status') ?? $task->getStatus());
        $task->setType($request->getDataInt('type') ?? $task->getType());
        $task->setPriority($request->getDataInt('priority') ?? $task->getPriority());
        $task->completion = $request->getDataInt('completion') ?? $task->completion;

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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaskElementCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTaskElementCreate($request))) {
            $response->data['task_element_create'] = new FormValidation($val);
            $response->header->status              = RequestStatusCode::R_400;

            return;
        }

        /** @var \Modules\Tasks\Models\Task $task */
        $task    = TaskMapper::get()->where('id', (int) ($request->getData('task')))->execute();
        $element = $this->createTaskElementFromRequest($request, $task);

        $task->due        = $element->due;
        $task->completion = $request->getDataInt('completion') ?? $task->completion;
        $task->setPriority($element->getPriority());
        $task->setStatus($element->getStatus());

        if ($task->getStatus() === TaskStatus::DONE) {
            $task->completion = 100;
        }

        $this->createModel($request->header->account, $element, TaskElementMapper::class, 'taskelement', $request->getOrigin());

        if (!empty($request->files)
            || !empty($request->getDataJson('media'))
        ) {
            $this->createTaskElementMedia($task, $element, $request);
        }

        $this->updateModel($request->header->account, $task, $task, TaskMapper::class, 'task', $request->getOrigin());

        if (!empty($task->trigger)) {
            $this->app->eventManager->triggerSimilar($task->trigger, '', $task);
        }

        $this->fillJsonResponse(
            $request,
            $response,
            NotificationLevel::OK,
            '',
            $this->app->l11nManager->getText($response->header->l11n->language, '0', '0', 'SucessfulCreate'),
            $element
        );
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

        if (!empty($uploadedFiles = $request->files)) {
            $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
                [],
                [],
                $uploadedFiles,
                $request->header->account,
                __DIR__ . '/../../../Modules/Media/Files' . $path,
                $path,
            );

            $collection = null;
            foreach ($uploaded as $media) {
                $this->createModelRelation(
                    $request->header->account,
                    $element->id,
                    $media->id,
                    TaskElementMapper::class,
                    'media',
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

                if ($collection === null) {
                    $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                        $accountPath,
                        $request->header->account,
                        __DIR__ . '/../../../Modules/Media/Files/Accounts/' . $account->id
                            . '/Tasks/' . $task->createdAt->format('Y') . '/'
                            . $task->createdAt->format('m') . '/'
                            . $task->id
                    );
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

        if (!empty($mediaFiles = $request->getDataJson('media'))) {
            $collection = null;

            foreach ($mediaFiles as $file) {
                /** @var \Modules\Media\Models\Media $media */
                $media = MediaMapper::get()->where('id', (int) $file)->limit(1)->execute();

                $this->createModelRelation(
                    $request->header->account,
                    $element->id,
                    $media->id,
                    TaskElementMapper::class,
                    'media',
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
                    $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                        $path,
                        $request->header->account,
                        __DIR__ . '/../../../Modules/Media/Files' . $path
                    );
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
        $element            = new TaskElement();
        $element->createdBy = new NullAccount($request->header->account);
        $element->due       = $request->getDataDateTime('due') ?? $task->due;
        $element->setPriority($request->getDataInt('priority') ?? $task->getPriority());
        $element->setStatus((int) ($request->getData('status')));
        $element->task           = $task->id;
        $element->description    = Markdown::parse($request->getDataString('plain') ?? '');
        $element->descriptionRaw = $request->getDataString('plain') ?? '';

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
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaskElementGet(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        /** @var TaskElement $task */
        $task = TaskElementMapper::get()->where('id', (int) $request->getData('id'))->execute();
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Task element', 'Task element successfully returned.', $task);
    }

    /**
     * Api method to update a task element
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaskElementSet(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        /** @var TaskElement $old */
        $old = TaskElementMapper::get()->where('id', (int) $request->getData('id'))->execute();

        /** @var TaskElement $new */
        $new = $this->updateTaskElementFromRequest($request, clone $old);
        $this->updateModel($request->header->account, $old, $new, TaskElementMapper::class, 'taskelement', $request->getOrigin());

        if ($old->getStatus() !== $new->getStatus()
            || $old->getPriority() !== $new->getPriority()
            || $old->due !== $new->due
        ) {
            /** @var Task $task */
            $task = TaskMapper::get()->where('id', $new->task)->execute();

            $task->setStatus($new->getStatus());
            $task->setPriority($new->getPriority());
            $task->due = $new->due;

            $this->updateModel($request->header->account, $task, $task, TaskMapper::class, 'task', $request->getOrigin());

            if (!empty($task->trigger)) {
                $this->app->eventManager->triggerSimilar($task->trigger, '', $task);
            }
        }

        $this->fillJsonResponse(
            $request,
            $response,
            NotificationLevel::OK,
            '',
            $this->app->l11nManager->getText($response->header->l11n->language, '0', '0', 'SucessfulUpdate'),
            $new
        );
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
        $element->due = $request->getDataDateTime('due') ?? $element->due;
        $element->setStatus($request->getDataInt('status') ?? $element->getStatus());
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

    /**
     * Api method to create task attribute
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaskAttributeCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTaskAttributeCreate($request))) {
            $response->data['attribute_create'] = new FormValidation($val);
            $response->header->status           = RequestStatusCode::R_400;

            return;
        }

        $attribute = $this->createTaskAttributeFromRequest($request);
        $this->createModel($request->header->account, $attribute, TaskAttributeMapper::class, 'attribute', $request->getOrigin());

        $this->fillJsonResponse(
            $request,
            $response,
            NotificationLevel::OK,
            '',
            $this->app->l11nManager->getText($response->header->l11n->language, '0', '0', 'SucessfulCreate'),
            $attribute
        );
    }

    /**
     * Method to create task attribute from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return TaskAttribute
     *
     * @since 1.0.0
     */
    private function createTaskAttributeFromRequest(RequestAbstract $request) : TaskAttribute
    {
        $attribute       = new TaskAttribute();
        $attribute->task = (int) $request->getData('task');
        $attribute->type = new NullTaskAttributeType((int) $request->getData('type'));

        if ($request->hasData('value')) {
            $attribute->value = new NullTaskAttributeValue((int) $request->getData('value'));
        } else {
            $newRequest = clone $request;
            $newRequest->setData('value', $request->getData('custom'), true);

            $value = $this->createTaskAttributeValueFromRequest($request);

            $attribute->value = $value;
        }

        return $attribute;
    }

    /**
     * Validate task attribute create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTaskAttributeCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['type'] = !$request->hasData('type'))
            || ($val['value'] = (!$request->hasData('value') && !$request->hasData('custom')))
            || ($val['task'] = !$request->hasData('task'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create task attribute l11n
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaskAttributeTypeL11nCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTaskAttributeTypeL11nCreate($request))) {
            $response->data['attr_type_l11n_create'] = new FormValidation($val);
            $response->header->status                = RequestStatusCode::R_400;

            return;
        }

        $attrL11n = $this->createTaskAttributeTypeL11nFromRequest($request);
        $this->createModel($request->header->account, $attrL11n, TaskAttributeTypeL11nMapper::class, 'attr_type_l11n', $request->getOrigin());

        $this->fillJsonResponse(
            $request,
            $response,
            NotificationLevel::OK,
            '',
            $this->app->l11nManager->getText($response->header->l11n->language, '0', '0', 'SucessfulCreate'),
            $attrL11n
        );
    }

    /**
     * Method to create task attribute l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BaseStringL11n
     *
     * @since 1.0.0
     */
    private function createTaskAttributeTypeL11nFromRequest(RequestAbstract $request) : BaseStringL11n
    {
        $attrL11n      = new BaseStringL11n();
        $attrL11n->ref = $request->getDataInt('type') ?? 0;
        $attrL11n->setLanguage(
            $request->getDataString('language') ?? $request->header->l11n->language
        );
        $attrL11n->content = $request->getDataString('title') ?? '';

        return $attrL11n;
    }

    /**
     * Validate task attribute l11n create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTaskAttributeTypeL11nCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['type'] = !$request->hasData('type'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create task attribute type
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaskAttributeTypeCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTaskAttributeTypeCreate($request))) {
            $response->data['attr_type_create'] = new FormValidation($val);
            $response->header->status           = RequestStatusCode::R_400;

            return;
        }

        $attrType = $this->createTaskAttributeTypeFromRequest($request);
        $this->createModel($request->header->account, $attrType, TaskAttributeTypeMapper::class, 'attr_type', $request->getOrigin());

        $this->fillJsonResponse(
            $request,
            $response,
            NotificationLevel::OK,
            '',
            $this->app->l11nManager->getText($response->header->l11n->language, '0', '0', 'SucessfulCreate'),
            $attrType
        );
    }

    /**
     * Method to create task attribute from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return TaskAttributeType
     *
     * @since 1.0.0
     */
    private function createTaskAttributeTypeFromRequest(RequestAbstract $request) : TaskAttributeType
    {
        $attrType = new TaskAttributeType($request->getDataString('name') ?? '');
        $attrType->setL11n($request->getDataString('title') ?? '', $request->getDataString('language') ?? ISO639x1Enum::_EN);
        $attrType->setFields($request->getDataInt('fields') ?? 0);
        $attrType->custom            = $request->getDataBool('custom') ?? false;
        $attrType->isRequired        = (bool) ($request->getData('is_required') ?? false);
        $attrType->validationPattern = $request->getDataString('validation_pattern') ?? '';

        return $attrType;
    }

    /**
     * Validate task attribute create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTaskAttributeTypeCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['name'] = !$request->hasData('name'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create task attribute value
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaskAttributeValueCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTaskAttributeValueCreate($request))) {
            $response->data['attr_value_create'] = new FormValidation($val);
            $response->header->status            = RequestStatusCode::R_400;

            return;
        }

        $attrValue = $this->createTaskAttributeValueFromRequest($request);
        $this->createModel($request->header->account, $attrValue, TaskAttributeValueMapper::class, 'attr_value', $request->getOrigin());

        if ($attrValue->isDefault) {
            $this->createModelRelation(
                $request->header->account,
                (int) $request->getData('attributetype'),
                $attrValue->id,
                TaskAttributeTypeMapper::class, 'defaults', '', $request->getOrigin()
            );
        }

        $this->fillJsonResponse(
            $request,
            $response,
            NotificationLevel::OK,
            '',
            $this->app->l11nManager->getText($response->header->l11n->language, '0', '0', 'SucessfulCreate'),
            $attrValue
        );
    }

    /**
     * Method to create task attribute value from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return TaskAttributeValue
     *
     * @since 1.0.0
     */
    private function createTaskAttributeValueFromRequest(RequestAbstract $request) : TaskAttributeValue
    {
        /** @var TaskAttributeType $type */
        $type = TaskAttributeTypeMapper::get()
            ->where('id', $request->getDataInt('type') ?? 0)
            ->execute();

        $attrValue            = new TaskAttributeValue();
        $attrValue->isDefault = $request->getDataBool('default') ?? false;
        $attrValue->setValue($request->getData('value'), $type->datatype);

        if ($request->hasData('title')) {
            $attrValue->setL11n($request->getDataString('title') ?? '', $request->getDataString('language') ?? ISO639x1Enum::_EN);
        }

        return $attrValue;
    }

    /**
     * Validate task attribute value create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTaskAttributeValueCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['attributetype'] = !$request->hasData('attributetype'))
            || ($val['value'] = !$request->hasData('value'))
        ) {
            return $val;
        }

        return [];
    }

    /**
     * Api method to create task attribute l11n
     *
     * @param RequestAbstract  $request  Request
     * @param ResponseAbstract $response Response
     * @param mixed            $data     Generic data
     *
     * @return void
     *
     * @api
     *
     * @since 1.0.0
     */
    public function apiTaskAttributeValueL11nCreate(RequestAbstract $request, ResponseAbstract $response, mixed $data = null) : void
    {
        if (!empty($val = $this->validateTaskAttributeValueL11nCreate($request))) {
            $response->data['attr_value_l11n_create'] = new FormValidation($val);
            $response->header->status                 = RequestStatusCode::R_400;

            return;
        }

        $attrL11n = $this->createTaskAttributeValueL11nFromRequest($request);
        $this->createModel($request->header->account, $attrL11n, TaskAttributeValueL11nMapper::class, 'attr_value_l11n', $request->getOrigin());

        $this->fillJsonResponse(
            $request,
            $response,
            NotificationLevel::OK,
            '',
            $this->app->l11nManager->getText($response->header->l11n->language, '0', '0', 'SucessfulCreate'),
            $attrL11n
        );
    }

    /**
     * Method to create task attribute l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return BaseStringL11n
     *
     * @since 1.0.0
     */
    private function createTaskAttributeValueL11nFromRequest(RequestAbstract $request) : BaseStringL11n
    {
        $attrL11n      = new BaseStringL11n();
        $attrL11n->ref = $request->getDataInt('value') ?? 0;
        $attrL11n->setLanguage(
            $request->getDataString('language') ?? $request->header->l11n->language
        );
        $attrL11n->content = $request->getDataString('title') ?? '';

        return $attrL11n;
    }

    /**
     * Validate task attribute l11n create request
     *
     * @param RequestAbstract $request Request
     *
     * @return array<string, bool>
     *
     * @since 1.0.0
     */
    private function validateTaskAttributeValueL11nCreate(RequestAbstract $request) : array
    {
        $val = [];
        if (($val['title'] = !$request->hasData('title'))
            || ($val['value'] = !$request->hasData('value'))
        ) {
            return $val;
        }

        return [];
    }
}
