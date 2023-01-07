<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\Controller;

use Modules\Admin\Models\AccountMapper;
use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\CollectionMapper;
use Modules\Media\Models\MediaMapper;
use Modules\Media\Models\NullCollection;
use Modules\Media\Models\NullMedia;
use Modules\Media\Models\Reference;
use Modules\Media\Models\ReferenceMapper;
use Modules\Tag\Models\NullTag;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskElement;
use Modules\Tasks\Models\TaskElementMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
use Modules\Tasks\Models\TaskAttribute;
use Modules\Tasks\Models\TaskAttributeMapper;
use Modules\Tasks\Models\TaskAttributeType;
use Modules\Tasks\Models\TaskAttributeTypeL11n;
use Modules\Tasks\Models\TaskAttributeTypeL11nMapper;
use Modules\Tasks\Models\TaskAttributeTypeMapper;
use Modules\Tasks\Models\TaskAttributeValue;
use Modules\Tasks\Models\TaskAttributeValueL11n;
use Modules\Tasks\Models\TaskAttributeValueL11nMapper;
use Modules\Tasks\Models\TaskAttributeValueMapper;
use Modules\Tasks\Models\NullTaskAttributeType;
use Modules\Tasks\Models\NullTaskAttributeValue;
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
 * @license OMS License 1.0
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
        if (($val['title'] = empty($request->getData('title')))
            || ($val['plain'] = empty($request->getData('plain')))
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
            $response->set($request->uri->__toString(), new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        /** @var Task $task */
        $task = $this->createTaskFromRequest($request);
        $this->createModel($request->header->account, $task, TaskMapper::class, 'task', $request->getOrigin());

        if (!empty($request->getFiles())
            || !empty($request->getDataJson('media'))
        ) {
            $this->createTaskMedia($task, $request);
        }

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Task', 'Task successfully created.', $task);
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

        if (!empty($uploadedFiles = $request->getFiles())) {
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
                MediaMapper::create()->execute($media);
                TaskMapper::writer()->createRelationTable('media', [$media->getId()], $task->getId());

                $accountPath = '/Accounts/'
                    . $account->getId() . ' '
                    . $account->login . '/Tasks/'
                    . $task->createdAt->format('Y') . '/'
                    . $task->createdAt->format('m') . '/'
                    . $task->getId();

                $ref            = new Reference();
                $ref->name      = $media->name;
                $ref->source    = new NullMedia($media->getId());
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($accountPath);

                ReferenceMapper::create()->execute($ref);

                if ($collection === null) {
                    /** @var \Modules\Media\Models\Media $media */
                    $collection = MediaMapper::getParentCollection($path)->limit(1)->execute();

                    if ($collection instanceof NullCollection) {
                        $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                            $accountPath,
                            $request->header->account,
                            __DIR__ . '/../../../Modules/Media/Files/Accounts/'
                                . $account->getId() . '/Tasks/'
                                . $task->createdAt->format('Y') . '/'
                                . $task->createdAt->format('m') . '/'
                                . $task->getId()
                        );
                    }
                }

                CollectionMapper::writer()->createRelationTable('sources', [$ref->getId()], $collection->getId());
            }
        }

        if (!empty($mediaFiles = $request->getDataJson('media'))) {
            $collection = null;

            foreach ($mediaFiles as $file) {
                /** @var \Modules\Media\Models\Media $media */
                $media = MediaMapper::get()->where('id', (int) $file)->limit(1)->execute();

                TaskMapper::writer()->createRelationTable('media', [$media->getId()], $task->getId());

                $ref            = new Reference();
                $ref->name      = $media->name;
                $ref->source    = new NullMedia($media->getId());
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($path);

                ReferenceMapper::create()->execute($ref);

                if ($collection === null) {
                    /** @var \Modules\Media\Models\Media $media */
                    $collection = MediaMapper::getParentCollection($path)->limit(1)->execute();

                    if ($collection instanceof NullCollection) {
                        $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                            $path,
                            $request->header->account,
                            __DIR__ . '/../../../Modules/Media/Files' . $path
                        );
                    }
                }

                CollectionMapper::writer()->createRelationTable('sources', [$ref->getId()], $collection->getId());
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
            . $task->getId();
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
        $task->title          = (string) ($request->getData('title') ?? '');
        $task->description    = Markdown::parse((string) ($request->getData('plain') ?? ''));
        $task->descriptionRaw = (string) ($request->getData('plain') ?? '');
        $task->setCreatedBy(new NullAccount($request->header->account));
        $task->setStatus(TaskStatus::OPEN);
        $task->setType(TaskType::SINGLE);
        $task->redirect = (string) ($request->getData('redirect') ?? '');

        if (empty($request->getData('priority'))) {
            $task->due = empty($request->getData('due')) ? null : new \DateTime($request->getData('due'));
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
        $element->addTo(new NullAccount((int) ($request->getData('forward') ?? $request->header->account)));
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

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Task', 'Task successfully updated.', $new);
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
        $task->due            = $request->getData('due') !== null ? new \DateTime((string) ($request->getData('due'))) : $task->due;
        $task->setStatus((int) ($request->getData('status') ?? $task->getStatus()));
        $task->setType((int) ($request->getData('type') ?? $task->getType()));
        $task->setPriority((int) ($request->getData('priority') ?? $task->getPriority()));
        $task->completion = (int) ($request->getData('completion') ?? $task->completion);

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
            || ($val['forward'] = !(\is_numeric(empty($request->getData('forward')) ? $request->header->account : $request->getData('forward'))))
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
            $response->set('task_element_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        /** @var \Modules\Tasks\Models\Task $task */
        $task    = TaskMapper::get()->where('id', (int) ($request->getData('task')))->execute();
        $element = $this->createTaskElementFromRequest($request, $task);

        $task->due        = $element->due;
        $task->completion = (int) ($request->getData('completion') ?? $task->completion);
        $task->setPriority($element->getPriority());
        $task->setStatus($element->getStatus());

        if ($task->getStatus() === TaskStatus::DONE) {
            $task->completion = 100;
        }

        $this->createModel($request->header->account, $element, TaskElementMapper::class, 'taskelement', $request->getOrigin());

        if (!empty($request->getFiles())
            || !empty($request->getDataJson('media'))
        ) {
            $this->createTaskElementMedia($task, $element, $request);
        }

        $this->updateModel($request->header->account, $task, $task, TaskMapper::class, 'task', $request->getOrigin());

        if (!empty($task->trigger)) {
            $this->app->eventManager->triggerSimilar($task->trigger, '', $task);
        }

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Task element', 'Task element successfully created.', $element);
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

        if (!empty($uploadedFiles = $request->getFiles())) {
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
                MediaMapper::create()->execute($media);
                TaskElementMapper::writer()->createRelationTable('media', [$media->getId()], $element->getId());

                $accountPath = '/Accounts/' . $account->getId() . ' '
                    . $account->login . '/Tasks/'
                    . $task->createdAt->format('Y') . '/'
                    . $task->createdAt->format('m') . '/'
                    . $task->getId();

                $ref            = new Reference();
                $ref->name      = $media->name;
                $ref->source    = new NullMedia($media->getId());
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($accountPath);

                ReferenceMapper::create()->execute($ref);

                if ($collection === null) {
                    $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                        $accountPath,
                        $request->header->account,
                        __DIR__ . '/../../../Modules/Media/Files/Accounts/' . $account->getId()
                            . '/Tasks/' . $task->createdAt->format('Y') . '/'
                            . $task->createdAt->format('m') . '/'
                            . $task->getId()
                    );
                }

                CollectionMapper::writer()->createRelationTable('sources', [$ref->getId()], $collection->getId());
            }
        }

        if (!empty($mediaFiles = $request->getDataJson('media'))) {
            $collection = null;

            foreach ($mediaFiles as $file) {
                /** @var \Modules\Media\Models\Media $media */
                $media = MediaMapper::get()->where('id', (int) $file)->limit(1)->execute();

                TaskElementMapper::writer()->createRelationTable('media', [$media->getId()], $element->getId());

                $ref            = new Reference();
                $ref->name      = $media->name;
                $ref->source    = new NullMedia($media->getId());
                $ref->createdBy = new NullAccount($request->header->account);
                $ref->setVirtualPath($path);

                ReferenceMapper::create()->execute($ref);

                if ($collection === null) {
                    $collection = $this->app->moduleManager->get('Media')->createRecursiveMediaCollection(
                        $path,
                        $request->header->account,
                        __DIR__ . '/../../../Modules/Media/Files' . $path
                    );
                }

                CollectionMapper::writer()->createRelationTable('sources', [$ref->getId()], $collection->getId());
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
        $element->due       = !empty($request->getData('due')) ? new \DateTime((string) ($request->getData('due'))) : $task->due;
        $element->setPriority((int) ($request->getData('priority') ?? $task->getPriority()));
        $element->setStatus((int) ($request->getData('status')));
        $element->task           = $task->getId();
        $element->description    = Markdown::parse((string) ($request->getData('plain') ?? ''));
        $element->descriptionRaw = (string) ($request->getData('plain') ?? '');

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

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Task element', 'Task element successfully updated.', $new);
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
        $element->due = $request->getData('due') !== null ? new \DateTime((string) ($request->getData('due'))) : $element->due;
        $element->setStatus((int) ($request->getData('status') ?? $element->getStatus()));
        $element->description    = Markdown::parse((string) ($request->getData('plain') ?? $element->descriptionRaw));
        $element->descriptionRaw = (string) ($request->getData('plain') ?? $element->descriptionRaw);

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
            $response->set('attribute_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attribute = $this->createTaskAttributeFromRequest($request);
        $this->createModel($request->header->account, $attribute, TaskAttributeMapper::class, 'attribute', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute', 'Attribute successfully created', $attribute);
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

        if ($request->getData('value') !== null) {
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
        if (($val['type'] = empty($request->getData('type')))
            || ($val['value'] = (empty($request->getData('value')) && empty($request->getData('custom'))))
            || ($val['task'] = empty($request->getData('task')))
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
            $response->set('attr_type_l11n_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attrL11n = $this->createTaskAttributeTypeL11nFromRequest($request);
        $this->createModel($request->header->account, $attrL11n, TaskAttributeTypeL11nMapper::class, 'attr_type_l11n', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute type localization', 'Attribute type localization successfully created', $attrL11n);
    }

    /**
     * Method to create task attribute l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return TaskAttributeTypeL11n
     *
     * @since 1.0.0
     */
    private function createTaskAttributeTypeL11nFromRequest(RequestAbstract $request) : TaskAttributeTypeL11n
    {
        $attrL11n       = new TaskAttributeTypeL11n();
        $attrL11n->type = (int) ($request->getData('type') ?? 0);
        $attrL11n->setLanguage((string) (
            $request->getData('language') ?? $request->getLanguage()
        ));
        $attrL11n->title = (string) ($request->getData('title') ?? '');

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
        if (($val['title'] = empty($request->getData('title')))
            || ($val['type'] = empty($request->getData('type')))
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
            $response->set('attr_type_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attrType = $this->createTaskAttributeTypeFromRequest($request);
        $this->createModel($request->header->account, $attrType, TaskAttributeTypeMapper::class, 'attr_type', $request->getOrigin());

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute type', 'Attribute type successfully created', $attrType);
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
        $attrType = new TaskAttributeType($request->getData('name') ?? '');
        $attrType->setL11n((string) ($request->getData('title') ?? ''), $request->getData('language') ?? ISO639x1Enum::_EN);
        $attrType->setFields((int) ($request->getData('fields') ?? 0));
        $attrType->custom            = (bool) ($request->getData('custom') ?? false);
        $attrType->isRequired        = (bool) ($request->getData('is_required') ?? false);
        $attrType->validationPattern = (string) ($request->getData('validation_pattern') ?? '');

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
        if (($val['title'] = empty($request->getData('title')))
            || ($val['name'] = empty($request->getData('name')))
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
            $response->set('attr_value_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attrValue = $this->createTaskAttributeValueFromRequest($request);
        $this->createModel($request->header->account, $attrValue, TaskAttributeValueMapper::class, 'attr_value', $request->getOrigin());

        if ($attrValue->isDefault) {
            $this->createModelRelation(
                $request->header->account,
                (int) $request->getData('attributetype'),
                $attrValue->getId(),
                TaskAttributeTypeMapper::class, 'defaults', '', $request->getOrigin()
            );
        }

        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute value', 'Attribute value successfully created', $attrValue);
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
        $type = (int) ($request->getData('attributetype') ?? 0);

        $attrValue            = new TaskAttributeValue($type, $request->getData('value'));
        $attrValue->isDefault = (bool) ($request->getData('default') ?? false);

        if ($request->getData('title') !== null) {
            $attrValue->setL11n($request->getData('title'), $request->getData('language') ?? ISO639x1Enum::_EN);
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
        if (($val['attributetype'] = empty($request->getData('attributetype')))
            || ($val['value'] = empty($request->getData('value')))
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
            $response->set('attr_value_l11n_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $attrL11n = $this->createTaskAttributeValueL11nFromRequest($request);
        $this->createModel($request->header->account, $attrL11n, TaskAttributeValueL11nMapper::class, 'attr_value_l11n', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Attribute type localization', 'Attribute type localization successfully created', $attrL11n);
    }

    /**
     * Method to create task attribute l11n from request.
     *
     * @param RequestAbstract $request Request
     *
     * @return TaskAttributeValueL11n
     *
     * @since 1.0.0
     */
    private function createTaskAttributeValueL11nFromRequest(RequestAbstract $request) : TaskAttributeValueL11n
    {
        $attrL11n        = new TaskAttributeValueL11n();
        $attrL11n->value = (int) ($request->getData('value') ?? 0);
        $attrL11n->setLanguage((string) (
            $request->getData('language') ?? $request->getLanguage()
        ));
        $attrL11n->title = (string) ($request->getData('title') ?? '');

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
        if (($val['title'] = empty($request->getData('title')))
            || ($val['value'] = empty($request->getData('value')))
        ) {
            return $val;
        }

        return [];
    }
}
