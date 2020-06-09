<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
 */
declare(strict_types=1);

use \Modules\Tasks\Models\AccountRelation;
use \Modules\Tasks\Models\GroupRelation;
use \Modules\Tasks\Models\TaskPriority;
use \Modules\Tasks\Models\TaskStatus;

/** @var \Modules\Tasks\Views\TaskView $this */
/** @var \Modules\Tasks\Models\Task $task */
$task      = $this->getData('task');
$taskMedia = $task->getMedia();
$elements  = $task->getTaskElements();
$cElements = \count($elements);
$color     = $this->getStatus($task->getStatus());

echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-md-6 col-xs-12">
        <section id="task" class="portlet"
            data-update-content="#task"
            data-update-element="#task .task-title, #task .task-content"
            data-update-tpl="#headTpl, #contentTpl"
            data-tag="form"
            data-method="POST"
            data-uri="<?= \phpOMS\Uri\UriFactory::build('{/api}task?id={?id}&csrf={$CSRF}'); ?>">
            <?php if ($task->isEditable()) : ?>
                <template id="headTpl">
                    <h1 class="task-title"><input type="text" data-tpl-text="/title" data-tpl-value="/title" data-value="" name="title" autocomplete="off"></h1>
                </template>
                <template id="contentTpl">
                    <div class="task-content">
                        <!-- todo: bind js after adding template -->
                        <?= $this->getData('editor')->render('task-edit'); ?>
                        <?= $this->getData('editor')->getData('text')->render(
                            'task-edit',
                            'plain',
                            'taskElementEdit',
                            '', '',
                            '{/base}/api/task?id={?id}', '{/base}/api/task?id={?id}',
                        ); ?>
                    </div>
                </template>
            <?php endif; ?>
            <div class="portlet-head">
                <div class="row middle-xs">
                    <span class="col-xs-0">
                        <img class="profile-image" alt="<?= $this->getHtml('User', '0', '0'); ?>" data-lazyload="<?= $this->getAccountImage($task->getCreatedBy()); ?>">
                    </span>
                    <span>
                        <?= $this->printHtml($task->getCreatedBy()->getName1()); ?> - <?= $this->printHtml($task->getCreatedAt()->format('Y/m/d H:i')); ?>
                    </span>
                    <span class="col-xs end-xs plain-grid">
                        <span id="task-status-badge" class="nobreak tag task-status-<?= $this->printHtml($task->getStatus()); ?>">
                            <?= $this->getHtml('S' . $task->getStatus()) ?>
                        </span>
                    </span>
                </div>
            </div>
            <div class="portlet-body">
                <span class="task-title" data-tpl-text="/title" data-tpl-value="/title" data-value="">
                    <?= $this->printHtml($task->getTitle()); ?>
                </span>
                <article class="task-content"
                    data-tpl-text="{/base}/api/task?id={?id}"
                    data-tpl-value="{/base}/api/task?id={?id}"
                    data-tpl-value-path="/0/response/descriptionRaw"
                    data-tpl-text-path="/0/response/description"
                    data-value=""
                    ><?= $task->getDescription(); ?></article>
            </div>
            <div class="portlet-foot row">
                <div class="row col-xs plain-grid">
                    <div class="col-xs">
                        <?php if (!empty($taskMedia)) : ?>
                            <div>
                                <?php foreach ($taskMedia as $media) : ?>
                                    <span><?= $media->getName(); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div>
                            <?php if ($task->getPriority() === TaskPriority::NONE) : ?>
                                <?= $this->getHtml('Due') ?>: <?= $this->printHtml($task->getDue()->format('Y/m/d H:i')); ?>
                            <?php else : ?>
                                <?= $this->getHtml('Priority') ?>: <?= $this->getHtml('P' . $task->getPriority()) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-xs-0 end-xs plain-grid">
                        <?php if ($task->isEditable() && $this->request->getHeader()->getAccount() === $task->getCreatedBy()->getId()) : ?>
                            <div class="col-xs end-xs plain-grid">
                                <button class="save hidden"><?= $this->getHtml('Save', '0', '0') ?></button>
                                <button class="cancel hidden"><?= $this->getHtml('Cancel', '0', '0') ?></button>
                                <button class="update"><?= $this->getHtml('Edit', '0', '0') ?></button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <div id="elements">
            <template id="elementTpl">
                <section id="taskelmenet-0" class="box wf-100 taskElement"
                    data-update-content="#elements"
                    data-update-element=".taskElement .taskElement-content"
                    data-tag="form"
                    data-method="POST"
                    data-uri="<?= \phpOMS\Uri\UriFactory::build('{/api}task/element?{?}&csrf={$CSRF}'); ?>">
                    <div class="inner pAlignTable">
                        <div class="vC wf-100">
                            <span data-tpl-text="{/base}/api/task/element?id={$id}" data-tpl-text-path="/0/response/createdBy/name/0"></span>
                            -
                            <span data-tpl-text="{/base}/api/task/element?id={$id}" data-tpl-text-path="/0/response/createdAt/date"></span>
                        </div>
                        <span class="vC tag task-status-0">
                            <!-- status-->
                        </span>
                    </div>

                    <div class="inner taskElement-content">
                        <article data-tpl-text="{/base}/api/task/element?id={$id}" data-tpl-text-path="/0/response/description" data-value=""></article>
                    </div>

                    <div class="inner">
                        <!-- media here -->
                    </div>

                    <div class="inner pAlignTable" style="background: #efefef; border-top: 1px solid #dfdfdf;">
                        <div class="vC wf-100 nobreak">
                            <!-- due / priority -->
                        </div>

                        <div class="vC">
                            <input type="hidden" value="" name="id">
                            <button class="save hidden"><?= $this->getHtml('Save', '0', '0') ?></button>
                            <button class="cancel hidden"><?= $this->getHtml('Cancel', '0', '0') ?></button>
                            <button class="update"><?= $this->getHtml('Edit', '0', '0') ?></button>
                        </div>
                </section>
            </template>
            <?php if ($task->isEditable()) : ?>
                <template id="taskElementContentTpl">
                    <div class="inner taskElement-content">
                        <!-- todo: bind js after adding template -->
                        <?= $this->getData('editor')->render('task-edit'); ?>
                        <?= $this->getData('editor')->getData('text')->render(
                                'task-edit',
                                'plain',
                                'taskElementEdit',
                                '', '',
                                '/content', '{/api}task?id={?id}'
                            ); ?>
                    </div>
                </template>
            <?php endif; ?>
            <?php $c = 0; $previous = null;
            foreach ($elements as $key => $element) : ++$c;
                if ($element->getDescription() !== '') : ?>
                <section id="taskelmenet-<?= $c; ?>" class="portlet taskElement"
                    data-update-content="#elements"
                    data-update-element=".taskElement .taskElement-content"
                    data-tag="form"
                    data-method="POST"
                    data-uri="<?= \phpOMS\Uri\UriFactory::build('{/api}task/element?{?}&csrf={$CSRF}'); ?>">
                    <div class="portlet-head">
                        <div class="row middle-xs">
                            <span class="col-xs-0">
                                <img class="profile-image" alt="<?= $this->getHtml('User', '0', '0'); ?>" data-lazyload="<?= $this->getAccountImage($element->getCreatedBy()); ?>">
                            </span>
                            <span class="col-xs">
                                <?= $this->printHtml($element->getCreatedBy()->getName1()); ?> - <?= $this->printHtml($element->getCreatedAt()->format('Y-m-d H:i')); ?>
                            </span>
                            <span class="tag task-status-<?= $this->printHtml($element->getStatus()); ?>">
                                <?= $this->getHtml('S' . $element->getStatus()) ?>
                            </span>
                        </div>
                    </div>

                    <?php if ($element->getDescription() !== '') : ?>
                        <div class="portlet-body taskElement-content">
                            <article data-tpl-text="/content" data-tpl-value="{/api}task/element?id={?id}" data-tpl-value-path="/0/response/description" data-value=""><?= $element->getDescription(); ?></article>
                        </div>
                    <?php endif; ?>


                    <div class="portlet-foot row middle-xs">
                        <?php $elementMedia = $element->getMedia();
                        if (!empty($elementMedia)) : ?>
                            <div>
                                <?php foreach ($elementMedia as $media) : ?>
                                    <span><?= $media->getName(); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($element->getStatus() !== TaskStatus::CANCELED
                            || $element->getStatus() !== TaskStatus::DONE
                            || $element->getStatus() !== TaskStatus::SUSPENDED
                            || $c != $cElements
                        ) : ?>
                            <div>
                                <?php
                                    if ($element->getPriority() === TaskPriority::NONE
                                        && ($previous !== null
                                            && $previous->getDue()->format('Y/m/d H:i') !== $element->getDue()->format('Y/m/d H:i')
                                        )
                                    ) : ?>
                                    <?= $this->getHtml('Due') ?>: <?= $this->printHtml($element->getDue()->format('Y/m/d H:i')); ?>
                                <?php elseif ($previous !== null && $previous->getPriority() !== $element->getPriority()) : ?>
                                    <?= $this->getHtml('Priority') ?>: <?= $this->getHtml('P' . $element->getPriority()) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($task->isEditable()
                            && $this->request->getHeader()->getAccount() === $element->getCreatedBy()->getId()
                        ) : ?>
                            <div class="col-xs end-xs plain-grid">
                                <input type="hidden" value="<?= $element->getId(); ?>" name="id">
                                <button class="save hidden"><?= $this->getHtml('Save', '0', '0') ?></button>
                                <button class="cancel hidden"><?= $this->getHtml('Cancel', '0', '0') ?></button>
                                <button class="update"><?= $this->getHtml('Edit', '0', '0') ?></button>
                            </div>
                        <?php endif; ?>
                </section>
                <?php endif; ?>

                <?php
                    $tos = $element->getTo();
                    if (\count($tos) > 1
                        || (\count($tos) > 0 && $tos[0]->getRelation()->getId() !== $task->getCreatedBy()->getId())
                    ) : ?>
                    <section class="box wf-100">
                        <div class="inner">
                            <?= $this->getHtml('ForwardedTo') ?>
                            <?php foreach ($tos as $to) : ?>
                                <?php if ($to instanceof AccountRelation) : ?>
                                    <a href="<?= phpOMS\Uri\UriFactory::build('{/prefix}profile/single?{?}&for=' . $to->getRelation()->getId()) ?>"><?= $this->printHtml($to->getRelation()->getName1()); ?></a>
                                <?php elseif ($to instanceof GroupRelation) : ?>
                                    <?= $this->printHtml($to->getRelation()->getName()); ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; $previous = $element; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="col-md-6 col-xs-12">
        <div class="portlet">
            <form
                id="taskElementCreate" method="PUT"
                action="<?= \phpOMS\Uri\UriFactory::build('{/api}task/element?{?}&csrf={$CSRF}'); ?>"
                data-add-content="#elements"
                data-add-element=".taskElement-content"
                data-add-tpl="#elementTpl"
            >
                <div class="portlet-head"><?= $this->getHtml('Message') ?></div>
                <div class="portlet-body">
                    <table class="layout wf-100" style="table-layout: fixed">
                        <tr><td><?= $this->getData('editor')->render('task-editor'); ?>
                        <tr><td><?= $this->getData('editor')->getData('text')->render(
                            'task-editor',
                            'plain',
                            'taskElementCreate',
                            '', '',
                            '/content', '{/api}task?id={?id}'
                            ); ?>
                        <tr><td><label for="iPriority"><?= $this->getHtml('Priority') ?></label>
                        <tr><td>
                            <select id="iPriority" name="priority">
                                <option value="<?= TaskPriority::NONE; ?>"<?= $task->getPriority() === TaskPriority::NONE ? ' selected' : ''?>><?= $this->getHtml('P0') ?>
                                <option value="<?= TaskPriority::VLOW; ?>"<?= $task->getPriority() === TaskPriority::VLOW ? ' selected' : ''?>><?= $this->getHtml('P1') ?>
                                <option value="<?= TaskPriority::LOW; ?>"<?= $task->getPriority() === TaskPriority::LOW ? ' selected' : ''?>><?= $this->getHtml('P2') ?>
                                <option value="<?= TaskPriority::MEDIUM; ?>"<?= $task->getPriority() === TaskPriority::MEDIUM ? ' selected' : ''?>><?= $this->getHtml('P3') ?>
                                <option value="<?= TaskPriority::HIGH; ?>"<?= $task->getPriority() === TaskPriority::HIGH ? ' selected' : ''?>><?= $this->getHtml('P4') ?>
                                <option value="<?= TaskPriority::VHIGH; ?>"<?= $task->getPriority() === TaskPriority::VHIGH ? ' selected' : ''?>><?= $this->getHtml('P5') ?>
                            </select>
                        <tr><td><label for="iDue"><?= $this->getHtml('Due') ?></label>
                        <tr><td><input type="datetime-local" id="iDue" name="due" value="<?= $this->printHtml(
                                !empty($elements) ? \end($elements)->getDue()->format('Y-m-d\TH:i:s') : $task->getDue()->format('Y-m-d\TH:i:s')
                            ); ?>">
                        <tr><td><label for="iStatus"><?= $this->getHtml('Status') ?></label>
                        <tr><td><select id="iStatus" name="status">
                                    <option value="<?= TaskStatus::OPEN; ?>"<?= $task->getStatus() === TaskStatus::OPEN ? ' selected' : ''?>><?= $this->getHtml('S1') ?>
                                    <option value="<?= TaskStatus::WORKING; ?>"<?= $task->getStatus() === TaskStatus::WORKING ? ' selected' : ''?>><?= $this->getHtml('S2') ?>
                                    <option value="<?= TaskStatus::SUSPENDED; ?>"<?= $task->getStatus() === TaskStatus::SUSPENDED ? ' selected' : ''?>><?= $this->getHtml('S3') ?>
                                    <option value="<?= TaskStatus::CANCELED; ?>"<?= $task->getStatus() === TaskStatus::CANCELED ? ' selected' : ''?>><?= $this->getHtml('S4') ?>
                                    <option value="<?= TaskStatus::DONE; ?>"<?= $task->getStatus() === TaskStatus::DONE ? ' selected' : ''?>><?= $this->getHtml('S5') ?>
                                </select>
                        <tr><td><label for="iReceiver"><?= $this->getHtml('To') ?></label>
                        <tr><td><?= $this->getData('accGrpSelector')->render('iReceiver', 'to', true); ?>
                        <tr><td><label for="iMedia"><?= $this->getHtml('Media') ?></label>
                        <tr><td><div class="ipt-wrap">
                                <div class="ipt-first"><input type="text" id="iMedia" placeholder="&#xf15b; File"></div>
                                <div class="ipt-second"><button><?= $this->getHtml('Select') ?></button></div>
                            </div>
                        <tr><td><label for="iUpload"><?= $this->getHtml('Upload') ?></label>
                        <tr><td>
                            <input type="file" id="iUpload" name="fileUpload" form="fTask">
                    </table>
                </div>
                <div class="portlet-foot">
                    <input class="add" data-form="" type="submit" id="iTaskElementCreateButton" name="taskElementCreateButton" value="<?= $this->getHtml('Create', '0', '0'); ?>">
                    <input type="hidden" name="task" value="<?= $this->printHtml($this->request->getData('id')); ?>"><input type="hidden" name="type" value="1">
                </div>
            </form>
        </div>
    </div>
</div>
