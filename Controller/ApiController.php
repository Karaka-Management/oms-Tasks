<?php
/**
 * Orange Management
 *
 * PHP Version 8.0
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

namespace Modules\Tasks\Controller;

use Modules\Admin\Models\NullAccount;
use Modules\Media\Models\NullMedia;
use Modules\Tag\Models\NullTag;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskElement;
use Modules\Tasks\Models\TaskElementMapper;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;
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
 * @link    https://orange-management.org
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
    public function apiTaskCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateTaskCreate($request))) {
            $response->set($request->uri->__toString(), new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $task = $this->createTaskFromRequest($request);
        $this->createModel($request->header->account, $task, TaskMapper::class, 'task', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Task', 'Task successfully created.', $task);
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
                    $task->addTag($internalResponse->get($request->uri->__toString())['response']);
                } else {
                    $task->addTag(new NullTag((int) $tag['id']));
                }
            }
        }

        if (!empty($uploadedFiles = $request->getFiles() ?? [])) {
            $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
                [''],
                $uploadedFiles,
                $request->header->account,
                __DIR__ . '/../../../Modules/Media/Files/Modules/Tasks',
                '/Modules/Tasks',
            );

            foreach ($uploaded as $media) {
                $task->addMedia($media);
            }
        }

        if (!empty($mediaFiles = $request->getDataJson('media') ?? [])) {
            foreach ($mediaFiles as $media) {
                $task->addMedia(new NullMedia($media));
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
    public function apiTaskGet(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $task = TaskMapper::get((int) $request->getData('id'));
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
    public function apiTaskSet(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $old = clone TaskMapper::get((int) $request->getData('id'));
        $new = $this->updateTaskFromRequest($request);
        $this->updateModel($request->header->account, $old, $new, TaskMapper::class, 'task', $request->getOrigin());
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
    private function updateTaskFromRequest(RequestAbstract $request) : Task
    {
        $task                 = TaskMapper::get((int) ($request->getData('id')));
        $task->title          = (string) ($request->getData('title') ?? $task->title);
        $task->description    = Markdown::parse((string) ($request->getData('plain') ?? $task->descriptionRaw));
        $task->descriptionRaw = (string) ($request->getData('plain') ?? $task->descriptionRaw);
        $task->due            = new \DateTime((string) ($request->getData('due') ?? $task->due->format('Y-m-d H:i:s')));
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
    public function apiTaskElementCreate(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        if (!empty($val = $this->validateTaskElementCreate($request))) {
            $response->set('task_element_create', new FormValidation($val));
            $response->header->status = RequestStatusCode::R_400;

            return;
        }

        $task    = TaskMapper::get((int) ($request->getData('task')));
        $element = $this->createTaskElementFromRequest($request, $task);
        $task->setStatus($element->getStatus());
        $task->setPriority($element->getPriority());
        $task->due        = $element->due;
        $task->completion = (int) ($request->getData('completion') ?? $task->completion);

        if ($task->getStatus() === TaskStatus::DONE) {
            $task->completion = 100;
        }

        $this->createModel($request->header->account, $element, TaskElementMapper::class, 'taskelement', $request->getOrigin());
        $this->updateModel($request->header->account, $task, $task, TaskMapper::class, 'task', $request->getOrigin());
        $this->fillJsonResponse($request, $response, NotificationLevel::OK, 'Task element', 'Task element successfully created.', $element);
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

        if (!empty($uploadedFiles = $request->getFiles() ?? [])) {
            $uploaded = $this->app->moduleManager->get('Media')->uploadFiles(
                [''],
                $uploadedFiles,
                $request->header->account,
                __DIR__ . '/../../../Modules/Media/Files/Modules/Tasks',
                '/Modules/Tasks',
            );

            foreach ($uploaded as $media) {
                $element->addMedia($media);
            }
        }

        if (!empty($mediaFiles = $request->getDataJson('media') ?? [])) {
            foreach ($mediaFiles as $media) {
                $element->addMedia(new NullMedia($media));
            }
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
    public function apiTaskElementGet(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $task = TaskElementMapper::get((int) $request->getData('id'));
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
    public function apiTaskElementSet(RequestAbstract $request, ResponseAbstract $response, $data = null) : void
    {
        $old = clone TaskElementMapper::get((int) $request->getData('id'));
        $new = $this->updateTaskElementFromRequest($request);
        $this->updateModel($request->header->account, $old, $new, TaskElementMapper::class, 'taskelement', $request->getOrigin());

        if ($old->getSatus() !== $new->getStatus()
            || $old->getPriority() !== $new->getPriority()
            || $old->getDue() !== $new->getDue()
        ) {
            $task = TaskMapper::get($new->task);

            $task->setStatus($new->getStatus());
            $task->setPriority($new->getPriority());
            $task->due = $new->due;

            $this->updateModel($request->header->account, $task, $task, TaskMapper::class, 'task', $request->getOrigin());
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
    private function updateTaskElementFromRequest(RequestAbstract $request) : TaskElement
    {
        $element      = TaskElementMapper::get((int) ($request->getData('id')));
        $element->due = new \DateTime((string) ($request->getData('due') ?? $element->due->format('Y-m-d H:i:s')));
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
}
