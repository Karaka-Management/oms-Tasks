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

use Modules\Tasks\Models\TaskPriority;
use phpOMS\Uri\UriFactory;

/** @var \phpOMS\Views\View $this */
$open = $this->data['open'];

echo $this->data['nav']->render(); ?>

<div class="tabview tab-2">
    <div class="box">
        <ul class="tab-links">
            <li><label for="c-tab-1"><?= $this->getHtml('Overview'); ?></label>
            <!--<li><label for="c-tab-2"><?= $this->getHtml('Unread'); ?></label>-->
        </ul>
    </div>
    <div class="tab-content">
        <input type="radio" id="c-tab-1" name="tabular-2"<?= $this->request->uri->fragment === 'c-tab-1' ? ' checked' : ''; ?>>
        <div class="tab">
            <div class="row">
                <div class="col-xs-12">
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('YourOpen'); ?> (<?= \count($open); ?>)<i class="g-icon download btn end-xs">download</i></div>
                        <div class="slider">
                        <table id="taskList" class="default sticky">
                            <thead>
                                <td><?= $this->getHtml('Status'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                                <td><?= $this->getHtml('Due/Priority'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                                <td>
                                <td class="wf-100"><?= $this->getHtml('Title'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                                <td><?= $this->getHtml('Tag'); ?>
                                <td><?= $this->getHtml('Creator'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                                <td><?= $this->getHtml('Created'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                            <tbody>
                            <?php
                                $c = 0;
                                foreach ($open as $key => $task) : ++$c;
                                    $url = UriFactory::build(empty($task->redirect)
                                        ? '{/base}/task/view?{?}&id=' . $task->id
                                        : ('{/app}/' . $task->redirect),
                                        ['$id' => $task->id]
                                    );
                            ?>
                                <tr tabindex="0" data-href="<?= $url; ?>">
                                    <td data-label="<?= $this->getHtml('Status'); ?>">
                                        <a href="<?= $url; ?>">
                                            <span class="tag <?= $this->printHtml('task-status-' . $task->status); ?>">
                                                <?= $this->getHtml('S' . $task->status); ?>
                                            </span>
                                        </a>
                                    <td data-label="<?= $this->getHtml('Due/Priority'); ?>">
                                        <a href="<?= $url; ?>">
                                        <?php if ($task->priority === TaskPriority::NONE) : ?>
                                            <?= $this->printHtml($task->due->format('Y-m-d H:i')); ?>
                                        <?php else : ?>
                                            <?= $this->getHtml('P' . $task->priority); ?>
                                        <?php endif; ?>
                                        </a>
                                    <td><?= ($this->data['task_media'][$task->id] ?? false) === true ? '<i class="g-icon">attachment</i>' : ''; ?>
                                    <td data-label="<?= $this->getHtml('Title'); ?>">
                                        <a href="<?= $url; ?>"><?= \in_array($key, $this->data['unread']) ? '<strong>' : ''; ?><?= $this->printHtml($task->title); ?><?= \in_array($key, $this->data['unread']) ? '</strong>' : ''; ?></a>
                                    <td data-label="<?= $this->getHtml('Tag'); ?>">
                                        <?php foreach ($task->tags as $tag) : ?>
                                        <a href="<?= $url; ?>">
                                            <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>">
                                                <?= empty($tag->icon) ? '' : '<i class="g-icon">' . $this->printHtml($tag->icon) . '</i>'; ?>
                                                <?= $this->printHtml($tag->getL11n()); ?>
                                            </span>
                                        </a>
                                        <?php endforeach; ?>
                                    <td data-label="<?= $this->getHtml('Creator'); ?>">
                                        <a class="content" href="<?= UriFactory::build('{/base}/profile/view?{?}&for=' . $task->createdBy->id); ?>">
                                            <?= $this->printHtml($this->renderUserName(
                                                '%3$s %2$s %1$s',
                                                [
                                                    $task->createdBy->name1,
                                                    $task->createdBy->name2,
                                                    $task->createdBy->name3,
                                                    $task->createdBy->login ?? '',
                                                ])
                                            ); ?>
                                        </a>
                                    <td data-label="<?= $this->getHtml('Created'); ?>">
                                        <a href="<?= $url; ?>"><?= $this->printHtml($task->createdAt->format('Y-m-d H:i')); ?></a>
                                    <?php endforeach; if ($c == 0) : ?>
                                        <tr><td colspan="7" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                                    <?php endif; ?>
                        </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="portlet">
                        <div class="portlet-head"><?= $this->getHtml('OpenGiven'); ?> (<?= \count($this->data['given']); ?>)<i class="g-icon download btn end-xs">download</i></div>
                        <div class="slider">
                        <table id="taskList" class="default sticky">
                            <thead>
                                <td><?= $this->getHtml('Status'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                                <td><?= $this->getHtml('Due/Priority'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                                <td>
                                <td class="wf-100"><?= $this->getHtml('Title'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                                <td><?= $this->getHtml('Tag'); ?>
                                <td><?= $this->getHtml('For'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                                <td><?= $this->getHtml('Created'); ?><i class="sort-asc g-icon">expand_less</i><i class="sort-desc g-icon">expand_more</i>
                            <tbody>
                            <?php
                                $c = 0;
                                foreach ($this->data['given'] as $key => $task) : ++$c;
                                    $url = UriFactory::build(empty($task->redirect)
                                        ? '{/base}/task/view?{?}&id=' . $task->id
                                        : ('{/app}/' . $task->redirect),
                                        ['$id' => $task->id]
                                    );
                            ?>
                                <tr tabindex="0" data-href="<?= $url; ?>">
                                    <td data-label="<?= $this->getHtml('Status'); ?>">
                                        <a href="<?= $url; ?>">
                                            <span class="tag <?= $this->printHtml('task-status-' . $task->status); ?>">
                                                <?= $this->getHtml('S' . $task->status); ?>
                                            </span>
                                        </a>
                                    <td data-label="<?= $this->getHtml('Due/Priority'); ?>">
                                        <a href="<?= $url; ?>">
                                        <?php if ($task->priority === TaskPriority::NONE) : ?>
                                            <?= $this->printHtml($task->due->format('Y-m-d H:i')); ?>
                                        <?php else : ?>
                                            <?= $this->getHtml('P' . $task->priority); ?>
                                        <?php endif; ?>
                                        </a>
                                    <td><?= ($this->data['task_media'][$task->id] ?? false) === true ? '<i class="g-icon">attachment</i>' : ''; ?>
                                    <td data-label="<?= $this->getHtml('Title'); ?>">
                                        <a href="<?= $url; ?>"><?= \in_array($key, $this->data['unread']) ? '<strong>' : ''; ?><?= $this->printHtml($task->title); ?><?= \in_array($key, $this->data['unread']) ? '</strong>' : ''; ?></a>
                                    <td data-label="<?= $this->getHtml('Tag'); ?>">
                                        <?php foreach ($task->tags as $tag) : ?>
                                        <a href="<?= $url; ?>">
                                            <span class="tag" style="background: <?= $this->printHtml($tag->color); ?>">
                                                <?= empty($tag->icon) ? '' : '<i class="g-icon">' . $this->printHtml($tag->icon) . '</i>'; ?>
                                                <?= $this->printHtml($tag->getL11n()); ?>
                                            </span>
                                        </a>
                                        <?php endforeach; ?>
                                    <td><?php $responsibles = $task->getResponsible();
                                            foreach ($responsibles as $responsible) : ?>
                                            <a class="content" href="<?= UriFactory::build('{/base}/profile/view?for=' . $responsible->id); ?>">
                                                <?= $this->printHtml($responsible->name1); ?> <?= $this->printHtml($responsible->name2); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <td data-label="<?= $this->getHtml('Created'); ?>">
                                        <a href="<?= $url; ?>"><?= $this->printHtml($task->createdAt->format('Y-m-d H:i')); ?></a>
                                    <?php endforeach; if ($c == 0) : ?>
                                        <tr><td colspan="7" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
                                    <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
