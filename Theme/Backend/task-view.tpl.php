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

use Modules\Tasks\Models\AccountRelation;
use Modules\Tasks\Models\GroupRelation;
use Modules\Tasks\Models\TaskPriority;
use Modules\Tasks\Models\TaskStatus;
use phpOMS\Uri\UriFactory;

/** @var Modules\Tasks\Views\TaskView $this */
/** @var Modules\Tasks\Models\Task $task */
$task      = $this->data['task'];
$taskMedia = $task->files;
$elements  = $task->invertTaskElements();
$cElements = \count($elements);
$color     = $this->getStatus($task->status);

echo $this->data['nav']->render(); ?>
<div class="tabview tab-2">
    <div class="box">
        <ul class="tab-links">
            <li><label for="c-tab-1"><?= $this->getHtml('Task'); ?></label>
            <li><label for="c-tab-2"><?= $this->getHtml('Attributes', 'Attribute', 'Backend'); ?></label>
        </ul>
    </div>
    <div class="tab-content">
        <input type="radio" id="c-tab-1" name="tabular-2"<?= $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-md-6 col-xs-12">
                    <?php if (!empty($this->data['reminder'])) : ?>
                    <section class="portlet hl-4">
                        <div class="portlet-body"><?= $this->getHtml('ReminderedBy'); ?> <?= \reset($this->data['reminder'])->reminderBy->name1; ?></div>
                    </section>
                    <?php endif; ?>

                    <section id="task" class="portlet"
                        data-update-content="#task"
                        data-update-element="#task .task-title, #task .task-content"
                        data-update-tpl="#headTpl, #contentTpl"
                        data-tag="form"
                        data-method="POST"
                        data-uri="<?= UriFactory::build('{/api}task?id={?id}&csrf={$CSRF}'); ?>">
                        <?php if ($task->isEditable) : ?>
                            <template id="headTpl">
                                <h1 class="task-title"><input type="text" data-tpl-text="/title" data-tpl-value="/title" data-value="" name="title" autocomplete="off"></h1>
                            </template>
                            <template id="contentTpl">
                                <div class="task-content">
                                    <?= $this->data['editor']->render('task-edit'); ?>
                                    <?= $this->data['editor']->getData('text')->render(
                                        'task-edit',
                                        'plain',
                                        'taskEdit',
                                        '', '',
                                        '{/base}/api/task?id={?id}', '{/base}/api/task?id={?id}',
                                    ); ?>
                                </div>
                            </template>
                        <?php endif; ?>
                        <div class="portlet-head middle-xs">
                            <span class="col-xs-0">
                                <img class="profile-image" loading="lazy" alt="<?= $this->getHtml('User', '0', '0'); ?>" src="<?= $this->getAccountImage($task->createdBy->id); ?>">
                            </span>
                            <span>
                                <?= $this->printHtml($task->createdBy->name1); ?> - <?= $this->printHtml($task->createdAt->format('Y/m/d H:i')); ?>
                            </span>
                            <span class="end-xs plain-grid">
                                <form style="display: inline-block;" id="taskReminder" action="<?= UriFactory::build('{/api}task/reminder?{?}&csrf={$CSRF}'); ?>" method="POST">
                                    <i class="g-icon btn submit">notifications</i>
                                </form>
                                <span id="task-status-badge" class="nobreak tag task-status-<?= $task->status; ?>">
                                    <?= $this->getHtml('S' . $task->status); ?>
                                </span>
                            </span>
                        </div>
                        <div class="portlet-body">
                            <span class="task-title" data-tpl-text="/title" data-tpl-value="/title" data-value=""><?= $this->printHtml($task->title); ?></span>
                            <article class="task-content"
                                data-tpl-text="{/base}/api/task?id={?id}"
                                data-tpl-value="{/base}/api/task?id={?id}"
                                data-tpl-value-path="/0/response/descriptionRaw"
                                data-tpl-text-path="/0/response/description"
                                data-value=""><?= $task->description; ?></article>
                            <div class="tag-list">
                            <?php
                                foreach ($task->tags as $tag) : ?>
                                <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>">
                                    <?= empty($tag->icon) ? '' : '<i class="g-icon">' . $this->printHtml($tag->icon) . '</i>'; ?>
                                    <?= $this->printHtml($tag->getL11n()); ?>
                                </span>
                            <?php endforeach; ?>
                            </div>
                            <?php if (!empty($taskMedia)) : ?>
                                <div>
                                    <?php foreach ($taskMedia as $media) : ?>
                                        <span>
                                            <a class="content" href="<?= UriFactory::build('{/base}/media/view?id=' . $media->id);?>"><?= $media->name; ?></a>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="portlet-foot row">
                            <div class="row col-xs plain-grid">
                                <div class="col-xs">
                                    <div>
                                        <?php if ($task->priority === TaskPriority::NONE) : ?>
                                            <?= $this->getHtml('Due'); ?>: <?= $this->printHtml($task->due->format('Y/m/d H:i')); ?>
                                        <?php else : ?>
                                            <?= $this->getHtml('Priority'); ?>: <?= $this->getHtml('P' . $task->priority); ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-xs-0 end-xs plain-grid">
                                    <?php if ($task->isEditable && $this->request->header->account === $task->createdBy->id) : ?>
                                        <div class="col-xs end-xs plain-grid">
                                            <button class="save vh"><?= $this->getHtml('Save', '0', '0'); ?></button>
                                            <button class="cancel vh"><?= $this->getHtml('Cancel', '0', '0'); ?></button>
                                            <button class="update"><?= $this->getHtml('Edit', '0', '0'); ?></button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </section>

                    <div id="elements">
                        <template id="elementTpl">
                            <section id="taskelmenet-0" class="portlet taskElement"
                                data-update-content="#elements"
                                data-update-element=".taskElement .taskElement-content"
                                data-update-tpl="#taskElementContentTpl"
                                data-tag="form"
                                data-method="POST"
                                data-uri="<?= UriFactory::build('{/api}task/element?id={?id}&csrf={$CSRF}'); ?>">
                                <div class="portlet-head">
                                    <div class="row middle-xs">
                                        <span class="col-xs-0">
                                            <img class="profile-image" alt="<?= $this->getHtml('User', '0', '0'); ?>" src="<?= $this->getAccountImage($this->request->header->account); ?>">
                                        </span>
                                        <span class="col-xs">
                                            <span data-tpl-text="{/base}/api/task/element?id={$id}" data-tpl-text-path="/0/response/createdBy/name/0"></span>
                                            - <span data-tpl-text="{/base}/api/task/element?id={$id}" data-tpl-text-path="/0/response/createdAt/date"></span>
                                        </span>
                                    </div>
                                </div>

                                <div class="portlet-body">
                                    <article class="taskElement-content" data-tpl-text="{/base}/api/task/element?id={$id}" data-tpl-text-path="/0/response/description" data-value=""></article>
                                </div>

                                <div class="portlet-foot row middle-xs">
                                    <div class="nobreak">
                                        <!-- due / priority -->
                                    </div>

                                    <div class="col-xs end-xs plain-grid">
                                        <input type="hidden" value="" name="id">
                                        <button class="save vh"><?= $this->getHtml('Save', '0', '0'); ?></button>
                                        <button class="cancel vh"><?= $this->getHtml('Cancel', '0', '0'); ?></button>
                                        <button class="update"><?= $this->getHtml('Edit', '0', '0'); ?></button>
                                    </div>
                                </div>
                            </section>
                        </template>
                        <?php if ($task->isEditable) : ?>
                            <template id="taskElementContentTpl">
                                <div class="taskElement-content">
                                    <!-- @todo bind js after adding template -->
                                    <?= $this->data['editor']->render('task-element-edit'); ?>
                                    <?= $this->data['editor']->getData('text')->render(
                                            'task-element-edit',
                                            'plain',
                                            'taskElementEdit',
                                            '', '',
                                            '{/base}/api/task/element?id={$id}', '{/base}/api/task/element?id={$id}',
                                        ); ?>
                                </div>
                            </template>
                        <?php endif; ?>
                        <?php $c = 0; $previous = null;
                        foreach ($elements as $key => $element) : ++$c; ?>
                            <?php if ($c === 1
                                || ($previous !== null && $element->status !== $previous->status)
                            ) : ?>
                                <section class="portlet">
                                    <div class="portlet-body">
                                        <?= \sprintf($this->getHtml('status_change'),
                                            '<a href="' . UriFactory::build('profile/view?{?}&for=' . $element->createdBy->id) . '">' . $this->printHtml(
                                                $this->renderUserName(
                                                    '%3$s %2$s %1$s',
                                                    [$element->createdBy->name1, $element->createdBy->name2, $element->createdBy->name3, $element->createdBy->login])
                                                ) . '</a>',
                                            $element->createdAt->format('Y-m-d H:i')
                                        ); ?>
                                        <span class="tag task-status-<?= $element->status; ?>">
                                            <?= $this->getHtml('S' . $element->status); ?>
                                        </span>
                                    </div>
                                </section>
                            <?php endif; ?>

                            <?php if (($c === 1)
                                || ($previous !== null && $element->priority !== $previous->priority)
                            ) : ?>
                                <section class="portlet">
                                    <div class="portlet-body">
                                        <?= \sprintf($this->getHtml('priority_change'),
                                            '<a href="' . UriFactory::build('profile/view?{?}&for=' . $element->createdBy->id) . '">' . $this->printHtml(
                                                $this->renderUserName(
                                                    '%3$s %2$s %1$s',
                                                    [$element->createdBy->name1, $element->createdBy->name2, $element->createdBy->name3, $element->createdBy->login])
                                                ) . '</a>',
                                            $element->createdAt->format('Y-m-d H:i')
                                        ); ?>
                                        <span class="tag task-priority-<?= $element->priority; ?>">
                                            <?= $this->getHtml('P' . $element->priority); ?>
                                        </span>
                                    </div>
                                </section>
                            <?php endif; ?>

                            <?php if ($element->description !== '') : ?>
                            <section id="taskelmenet-<?= $element->id; ?>" class="portlet taskElement"
                                data-update-content="#elements"
                                data-update-element=".taskElement .taskElement-content"
                                data-update-tpl="#taskElementContentTpl"
                                data-tag="form"
                                data-method="POST"
                                data-id="<?= $element->id; ?>"
                                data-uri="<?= UriFactory::build('{/api}task/element?id=' . $element->id .'&csrf={$CSRF}'); ?>">
                                <div class="portlet-head">
                                    <div class="row middle-xs">
                                        <span class="col-xs-0">
                                            <img class="profile-image" loading="lazy" alt="<?= $this->getHtml('User', '0', '0'); ?>" src="<?= $this->getAccountImage($element->createdBy->id); ?>">
                                        </span>
                                        <span class="col-xs">
                                            <?= $this->printHtml(
                                                $this->renderUserName(
                                                    '%3$s %2$s %1$s',
                                                    [$element->createdBy->name1, $element->createdBy->name2, $element->createdBy->name3, $element->createdBy->login])
                                                ); ?> - <?= $this->printHtml($element->createdAt->format('Y-m-d H:i')); ?>
                                        </span>
                                    </div>
                                </div>

                                <?php
                                $elementMedia = $element->files;
                                if ($element->description !== '') : ?>
                                    <div class="portlet-body">
                                        <article class="taskElement-content" data-tpl-text="{/base}/api/task/element?id={$id}"
                                            data-tpl-value="{/base}/api/task/element?id={$id}"
                                            data-tpl-value-path="/0/response/descriptionRaw"
                                            data-tpl-text-path="/0/response/description"
                                            data-value=""><?= $element->description; ?></article>

                                        <?php if (!empty($elementMedia)) : ?>
                                            <div>
                                                <?php foreach ($elementMedia as $media) : ?>
                                                    <span><a class="content" href="<?= UriFactory::build('{/base}/media/view?id=' . $media->id);?>"><?= $media->name; ?></a></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php
                                    if ($task->isEditable
                                            && $this->request->header->account === $element->createdBy->id
                                    ) : ?>
                                <div class="portlet-foot row middle-xs">
                                    <?php if ($element->status !== TaskStatus::CANCELED
                                        || $element->status !== TaskStatus::DONE
                                        || $element->status !== TaskStatus::SUSPENDED
                                        || $c != $cElements
                                    ) : ?>
                                        <div>
                                            <?php
                                                if ($element->priority === TaskPriority::NONE
                                                    && ($previous !== null
                                                        && $previous->due->format('Y/m/d H:i') !== $element->due->format('Y/m/d H:i')
                                                    )
                                                ) : ?>
                                                <?= $this->getHtml('Due'); ?>: <?= $this->printHtml($element->due->format('Y/m/d H:i')); ?>
                                            <?php elseif ($previous !== null && $previous->priority !== $element->priority) : ?>
                                                <?= $this->getHtml('Priority'); ?>: <?= $this->getHtml('P' . $element->priority); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($task->isEditable
                                        && $this->request->header->account === $element->createdBy->id
                                    ) : ?>
                                        <div class="col-xs end-xs plain-grid">
                                            <input type="hidden" value="<?= $element->id; ?>" name="id">
                                            <button class="save vh"><?= $this->getHtml('Save', '0', '0'); ?></button>
                                            <button class="cancel vh"><?= $this->getHtml('Cancel', '0', '0'); ?></button>
                                            <button class="update"><?= $this->getHtml('Edit', '0', '0'); ?></button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </section>
                            <?php endif; ?>

                            <?php
                                $tos = $element->getTo();
                                if (\count($tos) > 1
                                    || (!empty($tos) && $tos[0]->getRelation()->id !== $element->createdBy->id)
                                ) : ?>
                                <section class="portlet wf-100">
                                    <div class="portlet-body">
                                        <a href="<?= UriFactory::build('{/base}/profile/view?{?}&for=' . $element->createdBy->id); ?>"><?= $this->printHtml(
                                            $this->renderUserName(
                                                '%3$s %2$s %1$s',
                                                [$element->createdBy->name1, $element->createdBy->name2, $element->createdBy->name3, $element->createdBy->login])
                                            ); ?></a> <?= $this->getHtml('forwarded_to'); ?>
                                        <?php foreach ($tos as $to) : ?>
                                            <?php if ($to instanceof AccountRelation) : ?>
                                                <a href="<?= UriFactory::build('{/base}/profile/view?{?}&for=' . $to->getRelation()->id); ?>"><?= $this->renderUserName(
                                                    '%3$s %2$s %1$s',
                                                    [$to->getRelation()->name1, $to->getRelation()->name2, $to->getRelation()->name3, $to->getRelation()->login]
                                                ); ?></a>
                                            <?php elseif ($to instanceof GroupRelation) : ?>
                                                <?= $this->printHtml($to->getRelation()->name); ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </section>
                            <?php endif; ?>
                        <?php $previous = $element; endforeach; ?>
                    </div>
                </div>

                <div class="col-md-6 col-xs-12">
                    <div class="portlet sticky">
                        <form
                            id="taskElementCreate" method="PUT"
                            action="<?= UriFactory::build('{/api}task/element?{?}&csrf={$CSRF}'); ?>"
                            data-add-content="#elements"
                            data-add-element=".taskElement-content"
                            data-add-tpl="#elementTpl"
                        >
                            <div class="portlet-head"><?= $this->getHtml('Message'); ?></div>
                            <div class="portlet-body">
                                <div class="form-group">
                                    <?= $this->data['editor']->render('task-editor'); ?>
                                </div>

                                <div class="form-group">
                                    <?= $this->data['editor']->getData('text')->render(
                                        'task-editor',
                                        'plain',
                                        'taskElementCreate',
                                        '', '',
                                        '/content', '{/api}task?id={?id}&csrf={$CSRF}'
                                        ); ?>
                                </div>

                                <div class="form-group">
                                    <label for="iStatus"><?= $this->getHtml('Status'); ?></label>
                                    <select id="iStatus" name="status">
                                        <option value="<?= TaskStatus::OPEN; ?>"<?= $task->status === TaskStatus::OPEN ? ' selected' : '';?>><?= $this->getHtml('S1'); ?>
                                        <option value="<?= TaskStatus::WORKING; ?>"<?= $task->status === TaskStatus::WORKING ? ' selected' : '';?>><?= $this->getHtml('S2'); ?>
                                        <option value="<?= TaskStatus::SUSPENDED; ?>"<?= $task->status === TaskStatus::SUSPENDED ? ' selected' : '';?>><?= $this->getHtml('S3'); ?>
                                        <option value="<?= TaskStatus::CANCELED; ?>"<?= $task->status === TaskStatus::CANCELED ? ' selected' : '';?>><?= $this->getHtml('S4'); ?>
                                        <option value="<?= TaskStatus::DONE; ?>"<?= $task->status === TaskStatus::DONE ? ' selected' : '';?>><?= $this->getHtml('S5'); ?>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="iiReceiver"><?= $this->getHtml('To'); ?></label>
                                    <?= $this->getData('accGrpSelector')->render('iReceiver', 'to', true); ?>
                                </div>

                                <div class="form-group wf-100">
                                <div class="more-container wf-100">
                                    <input id="more-customer-sales" type="checkbox" name="more-container">
                                    <label for="more-customer-sales">
                                        <span><?= $this->getHtml('Advanced'); ?></span>
                                        <i class="g-icon expand">chevron_right</i>
                                    </label>

                                    <div class="form-group">
                                        <label for="iPriority"><?= $this->getHtml('Priority'); ?></label>
                                        <select id="iPriority" name="priority">
                                            <option value="<?= TaskPriority::NONE; ?>"<?= $task->priority === TaskPriority::NONE ? ' selected' : '';?>><?= $this->getHtml('P0'); ?>
                                            <option value="<?= TaskPriority::VLOW; ?>"<?= $task->priority === TaskPriority::VLOW ? ' selected' : '';?>><?= $this->getHtml('P1'); ?>
                                            <option value="<?= TaskPriority::LOW; ?>"<?= $task->priority === TaskPriority::LOW ? ' selected' : '';?>><?= $this->getHtml('P2'); ?>
                                            <option value="<?= TaskPriority::MEDIUM; ?>"<?= $task->priority === TaskPriority::MEDIUM ? ' selected' : '';?>><?= $this->getHtml('P3'); ?>
                                            <option value="<?= TaskPriority::HIGH; ?>"<?= $task->priority === TaskPriority::HIGH ? ' selected' : '';?>><?= $this->getHtml('P4'); ?>
                                            <option value="<?= TaskPriority::VHIGH; ?>"<?= $task->priority === TaskPriority::VHIGH ? ' selected' : '';?>><?= $this->getHtml('P5'); ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="iDue"><?= $this->getHtml('Due'); ?></label>
                                        <input type="datetime-local" id="iDue" name="due" value="<?= $this->printHtml(
                                                empty($elements) ? $task->due->format('Y-m-d\TH:i:s') : \end($elements)->due->format('Y-m-d\TH:i:s')
                                            ); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="iCompletion"><?= $this->getHtml('Completion'); ?></label>
                                        <input id="iCompletion" name="completion" type="number" min="0" max="100">
                                    </div>
                                </div>
                                </div>

                                <div class="form-group">
                                    <label for="iMedia"><?= $this->getHtml('Media'); ?></label>
                                    <div class="ipt-wrap wf-100">
                                        <div class="ipt-first"><input type="text" id="iMedia"></div>
                                        <div class="ipt-second"><button><?= $this->getHtml('Select'); ?></button></div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="iUpload"><?= $this->getHtml('Upload'); ?></label>
                                    <input type="file" id="iUpload" name="fileUpload" form="fTask">
                                </div>
                            </div>
                            <div class="portlet-foot">
                                <input class="add" data-form="" type="submit" id="iTaskElementCreateButton" name="taskElementCreateButton" value="<?= $this->getHtml('Create', '0', '0'); ?>">
                                <input type="hidden" name="task" value="<?= $this->printHtml($this->request->getData('id')); ?>"><input type="hidden" name="type" value="1">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <input type="radio" id="c-tab-2" name="tabular-2" checked>
        <div class="tab">
            <div class="row">
                <?= $this->data['attributeView']->render(
                    $task->attributes,
                    $this->data['attributeTypes'] ?? [],
                    $this->data['units'] ?? [],
                    '{/api}task/attribute?csrf={$CSRF}',
                    $task->id
                );
                ?>
            </div>
        </div>

    </div>
</div>
