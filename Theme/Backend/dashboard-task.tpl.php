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

$tasksList = $this->data['tasks'] ?? [];
?>

<div id="tasks-dashboard" class="col-xs-12 col-md-6" draggable="true">
    <section class="portlet">
        <div class="portlet-head"><?= $this->getHtml('Tasks', 'Tasks'); ?></div>
        <div class="slider">
        <table class="default sticky">
            <thead>
                <td><?= $this->getHtml('Status', 'Tasks'); ?>
                <td><?= $this->getHtml('Due/Priority', 'Tasks'); ?>
                <td class="wf-100"><?= $this->getHtml('Title', 'Tasks'); ?>
            <tfoot>
            <tbody>
            <?php
            $c = 0;
            foreach ($tasksList as $key => $task) : ++$c;
                $url = UriFactory::build(empty($task->redirect)
                    ? '{/base}/task/view?{?}&id=' . $task->id
                    : ($task->redirect)
                );
            ?>
                <tr data-href="<?= $url; ?>">
                    <td><a href="<?= $url; ?>">
                        <span class="tag <?= $this->printHtml('task-status-' . $task->status); ?>">
                            <?= $this->getHtml('S' . $task->status, 'Tasks'); ?>
                        </span></a>
                    <td><a href="<?= $url; ?>">
                        <?php if ($task->priority === TaskPriority::NONE) : ?>
                            <?= $this->printHtml($task->due->format('Y-m-d H:i')); ?>
                        <?php else : ?>
                            <?= $this->getHtml('P' . $task->priority); ?>
                        <?php endif; ?>
                        </a>
                    <td><a href="<?= $url; ?>"><?= $this->printHtml($task->title); ?></a>
            <?php endforeach; ?>
            <?php if ($c == 0) : ?>
            <tr><td colspan="6" class="empty"><?= $this->getHtml('Empty', '0', '0'); ?>
            <?php endif; ?>
        </table>
        </div>
        <div class="portlet-foot">
            <a class="button" href="<?= UriFactory::build('{/base}/task/dashboard?{?}'); ?>"><?= $this->getHtml('More', '0', '0'); ?></a>
        </div>
    </section>
</div>