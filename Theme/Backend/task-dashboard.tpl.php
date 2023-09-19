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

use Modules\Tasks\Models\TaskPriority;
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */
/** @var \Modules\Tasks\Models\Task[] $tasks */
$tasks = $this->data['tasks'] ?? [];

$previous = empty($tasks) ? 'task/dashboard' : 'task/dashboard?{?}&id=' . \reset($tasks)->id . '&ptype=p';
$next     = empty($tasks) ? 'task/dashboard' : 'task/dashboard?{?}&id=' . \end($tasks)->id . '&ptype=n';

$open = $this->data['open'];

echo $this->data['nav']->render(); ?>

<div class="tabview tab-2">
    <div class="box">
        <ul class="tab-links">
            <li><label for="c-tab-1"><?= $this->getHtml('Overview'); ?></label></li>
            <li><label for="c-tab-2"><?= $this->getHtml('Unread'); ?></label></li>
        </ul>
    </div>
    <div class="tab-content">
        <input type="radio" id="c-tab-1" name="tabular-2"<?= $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12">
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('YourOpen'); ?> (<?= \count($open); ?>)<i class="lni lni-download download btn end-xs"></i></div>
                        <div class="slider">
                        <table id="taskList" class="default sticky">
                            <thead>
                                <td><?= $this->getHtml('Status'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Due/Priority'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td class="wf-100"><?= $this->getHtml('Title'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Tag'); ?>
                                <td><?= $this->getHtml('Creator'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Created'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                            <tbody>
                            <?php
                                $c = 0;
                                foreach ($open as $key => $task) : ++$c;
                                    $url = UriFactory::build(empty($task->redirect)
                                        ? 'task/single?{?}&id=' . $task->id
                                        : ('{/app}/' . $task->redirect),
                                        ['$id' => $task->id]
                                    );
                            ?>
                                <tr tabindex="0" data-href="<?= $url; ?>">
                                    <td data-label="<?= $this->getHtml('Status'); ?>">
                                        <a href="<?= $url; ?>">
                                            <span class="tag <?= $this->printHtml('task-status-' . $task->getStatus()); ?>">
                                                <?= $this->getHtml('S' . $task->getStatus()); ?>
                                            </span>
                                        </a>
                                    <td data-label="<?= $this->getHtml('Due/Priority'); ?>">
                                        <a href="<?= $url; ?>">
                                        <?php if ($task->getPriority() === TaskPriority::NONE) : ?>
                                            <?= $this->printHtml($task->due->format('Y-m-d H:i')); ?>
                                        <?php else : ?>
                                            <?= $this->getHtml('P' . $task->getPriority()); ?>
                                        <?php endif; ?>
                                        </a>
                                    <td data-label="<?= $this->getHtml('Title'); ?>">
                                        <a href="<?= $url; ?>"><?= $this->printHtml($task->title); ?></a>
                                    <td data-label="<?= $this->getHtml('Tag'); ?>">
                                        <?php $tags = $task->getTags(); foreach ($tags as $tag) : ?>
                                        <a href="<?= $url; ?>">
                                        <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>"><?= empty($tag->icon) ? '' : '<i class="' . $this->printHtml($tag->icon) . '"></i>'; ?><?= $this->printHtml($tag->getL11n()); ?></span>
                                        </a>
                                        <?php endforeach; ?>
                                    <td data-label="<?= $this->getHtml('Creator'); ?>">
                                        <a class="content" href="<?= UriFactory::build('{/base}/profile/single?{?}&for=' . $task->createdBy->id); ?>"><?= $this->printHtml($this->renderUserName('%3$s %2$s %1$s', [$task->createdBy->name1, $task->createdBy->name2, $task->createdBy->name3, $task->createdBy->login ?? ''])); ?></a>
                                    <td data-label="<?= $this->getHtml('Created'); ?>">
                                        <a href="<?= $url; ?>"><?= $this->printHtml($task->createdAt->format('Y-m-d H:i')); ?></a>
                                    <?php endforeach; if ($c == 0) : ?>
                                        <tr><td colspan="6" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                                    <?php endif; ?>
                        </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('OpenGiven'); ?> (<?= \count($open); ?>)<i class="lni lni-download download btn end-xs"></i></div>
                        <div class="slider">
                        <table id="taskList" class="default sticky">
                            <thead>
                                <td><?= $this->getHtml('Status'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Due/Priority'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td class="wf-100"><?= $this->getHtml('Title'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Tag'); ?>
                                <td><?= $this->getHtml('For'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Created'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                            <tbody>
                            <?php
                                $c = 0;
                                foreach ($open as $key => $task) : ++$c;
                                    $url = UriFactory::build(empty($task->redirect)
                                        ? 'task/single?{?}&id=' . $task->id
                                        : ('{/app}/' . $task->redirect),
                                        ['$id' => $task->id]
                                    );
                            ?>
                                <tr tabindex="0" data-href="<?= $url; ?>">
                                    <td data-label="<?= $this->getHtml('Status'); ?>">
                                        <a href="<?= $url; ?>">
                                            <span class="tag <?= $this->printHtml('task-status-' . $task->getStatus()); ?>">
                                                <?= $this->getHtml('S' . $task->getStatus()); ?>
                                            </span>
                                        </a>
                                    <td data-label="<?= $this->getHtml('Due/Priority'); ?>">
                                        <a href="<?= $url; ?>">
                                        <?php if ($task->getPriority() === TaskPriority::NONE) : ?>
                                            <?= $this->printHtml($task->due->format('Y-m-d H:i')); ?>
                                        <?php else : ?>
                                            <?= $this->getHtml('P' . $task->getPriority()); ?>
                                        <?php endif; ?>
                                        </a>
                                    <td data-label="<?= $this->getHtml('Title'); ?>">
                                        <a href="<?= $url; ?>"><?= $this->printHtml($task->title); ?></a>
                                    <td data-label="<?= $this->getHtml('Tag'); ?>">
                                        <?php $tags = $task->getTags(); foreach ($tags as $tag) : ?>
                                        <a href="<?= $url; ?>">
                                        <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>"><?= empty($tag->icon) ? '' : '<i class="' . $this->printHtml($tag->icon) . '"></i>'; ?><?= $this->printHtml($tag->getL11n()); ?></span>
                                        </a>
                                        <?php endforeach; ?>
                                    <td data-label="<?= $this->getHtml('Creator'); ?>">
                                        <a class="content" href="<?= UriFactory::build('{/base}/profile/single?{?}&for=' . $task->createdBy->id); ?>"><?= $this->printHtml($this->renderUserName('%3$s %2$s %1$s', [$task->createdBy->name1, $task->createdBy->name2, $task->createdBy->name3, $task->createdBy->login ?? ''])); ?></a>
                                    <td data-label="<?= $this->getHtml('Created'); ?>">
                                        <a href="<?= $url; ?>"><?= $this->printHtml($task->createdAt->format('Y-m-d H:i')); ?></a>
                                    <?php endforeach; if ($c == 0) : ?>
                                        <tr><td colspan="6" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                                    <?php endif; ?>
                        </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('All'); ?><i class="lni lni-download download btn end-xs"></i></div>
                        <div class="slider">
                        <table id="taskList" class="default sticky">
                            <thead>
                                <td><?= $this->getHtml('Status'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Due/Priority'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td class="wf-100"><?= $this->getHtml('Title'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Tag'); ?>
                                <td><?= $this->getHtml('Creator'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Created'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                            <tbody>
                            <?php
                                $c = 0;
                                foreach ($tasks as $key => $task) :
                                    if ($open !== null && isset($open[$task->id])) {
                                        continue;
                                    }

                                    ++$c;
                                    $url = UriFactory::build(empty($task->redirect)
                                        ? 'task/single?{?}&id=' . $task->id
                                        : ('{/app}/' . $task->redirect),
                                        ['$id' => $task->id]
                                    );
                            ?>
                                <tr tabindex="0" data-href="<?= $url; ?>">
                                    <td data-label="<?= $this->getHtml('Status'); ?>">
                                        <a href="<?= $url; ?>">
                                            <span class="tag <?= $this->printHtml('task-status-' . $task->getStatus()); ?>">
                                                <?= $this->getHtml('S' . $task->getStatus()); ?>
                                            </span>
                                        </a>
                                    <td data-label="<?= $this->getHtml('Due/Priority'); ?>">
                                        <a href="<?= $url; ?>">
                                        <?php if ($task->getPriority() === TaskPriority::NONE) : ?>
                                            <?= $this->printHtml($task->due->format('Y-m-d H:i')); ?>
                                        <?php else : ?>
                                            <?= $this->getHtml('P' . $task->getPriority()); ?>
                                        <?php endif; ?>
                                        </a>
                                    <td data-label="<?= $this->getHtml('Title'); ?>">
                                        <a href="<?= $url; ?>"><?= $this->printHtml($task->title); ?></a>
                                    <td data-label="<?= $this->getHtml('Tag'); ?>">
                                        <?php $tags = $task->getTags(); foreach ($tags as $tag) : ?>
                                        <a href="<?= $url; ?>">
                                        <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>"><?= empty($tag->icon) ? '' : '<i class="' . $this->printHtml($tag->icon) . '"></i>'; ?><?= $this->printHtml($tag->getL11n()); ?></span>
                                        </a>
                                        <?php endforeach; ?>
                                    <td data-label="<?= $this->getHtml('Creator'); ?>">
                                        <a class="content" href="<?= UriFactory::build('{/base}/profile/single?{?}&for=' . $task->createdBy->id); ?>"><?= $this->printHtml($this->renderUserName('%3$s %2$s %1$s', [$task->createdBy->name1, $task->createdBy->name2, $task->createdBy->name3, $task->createdBy->login ?? ''])); ?></a>
                                    <td data-label="<?= $this->getHtml('Created'); ?>">
                                        <a href="<?= $url; ?>"><?= $this->printHtml($task->createdAt->format('Y-m-d H:i')); ?></a>
                            <?php endforeach; if ($c == 0) : ?>
                                <tr><td colspan="6" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                            <?php endif; ?>
                        </table>
                        </div>
                        <div class="portlet-foot">
                            <a class="button" href="<?= UriFactory::build($previous); ?>"><?= $this->getHtml('Previous', '0', '0'); ?></a>
                            <a class="button" href="<?= UriFactory::build($next); ?>"><?= $this->getHtml('Next', '0', '0'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <input type="radio" id="c-tab-2" name="tabular-2"<?= $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12">
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('UnreadChanges'); ?> (<?= \count($open); ?>)<i class="lni lni-download download btn end-xs"></i></div>
                        <div class="slider">
                        <table id="taskList" class="default sticky">
                            <thead>
                                <td><?= $this->getHtml('Status'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Due/Priority'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td class="wf-100"><?= $this->getHtml('Title'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Tag'); ?>
                                <td><?= $this->getHtml('Creator'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                                <td><?= $this->getHtml('Created'); ?><i class="sort-asc fa fa-chevron-up"></i><i class="sort-desc fa fa-chevron-down"></i>
                            <tbody>
                            <?php
                                $c = 0;
                                foreach ($open as $key => $task) : ++$c;
                                    if (isset($this->data['unread'][$key])) {
                                        continue;
                                    }

                                    $url = UriFactory::build(empty($task->redirect)
                                        ? 'task/single?{?}&id=' . $task->id
                                        : ('{/app}/' . $task->redirect),
                                        ['$id' => $task->id]
                                    );
                            ?>
                                <tr tabindex="0" data-href="<?= $url; ?>">
                                    <td data-label="<?= $this->getHtml('Status'); ?>">
                                        <a href="<?= $url; ?>">
                                            <span class="tag <?= $this->printHtml('task-status-' . $task->getStatus()); ?>">
                                                <?= $this->getHtml('S' . $task->getStatus()); ?>
                                            </span>
                                        </a>
                                    <td data-label="<?= $this->getHtml('Due/Priority'); ?>">
                                        <a href="<?= $url; ?>">
                                        <?php if ($task->getPriority() === TaskPriority::NONE) : ?>
                                            <?= $this->printHtml($task->due->format('Y-m-d H:i')); ?>
                                        <?php else : ?>
                                            <?= $this->getHtml('P' . $task->getPriority()); ?>
                                        <?php endif; ?>
                                        </a>
                                    <td data-label="<?= $this->getHtml('Title'); ?>">
                                        <a href="<?= $url; ?>"><?= $this->printHtml($task->title); ?></a>
                                    <td data-label="<?= $this->getHtml('Tag'); ?>">
                                        <?php $tags = $task->getTags(); foreach ($tags as $tag) : ?>
                                        <a href="<?= $url; ?>">
                                        <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>"><?= empty($tag->icon) ? '' : '<i class="' . $this->printHtml($tag->icon) . '"></i>'; ?><?= $this->printHtml($tag->getL11n()); ?></span>
                                        </a>
                                        <?php endforeach; ?>
                                    <td data-label="<?= $this->getHtml('Creator'); ?>">
                                        <a class="content" href="<?= UriFactory::build('{/base}/profile/single?{?}&for=' . $task->createdBy->id); ?>"><?= $this->printHtml($this->renderUserName('%3$s %2$s %1$s', [$task->createdBy->name1, $task->createdBy->name2, $task->createdBy->name3, $task->createdBy->login ?? ''])); ?></a>
                                    <td data-label="<?= $this->getHtml('Created'); ?>">
                                        <a href="<?= $url; ?>"><?= $this->printHtml($task->createdAt->format('Y-m-d H:i')); ?></a>
                                    <?php endforeach; if ($c == 0) : ?>
                                        <tr><td colspan="6" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                                    <?php endif; ?>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
