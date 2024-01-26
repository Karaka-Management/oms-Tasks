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
use Modules\Tasks\Models\TaskStatus;
use phpOMS\Uri\UriFactory;

?>

<div id="news-dashboard" class="col-xs-12 col-md-6" draggable="true">
    <div class="portlet">
        <div class="portlet-head"><?= $this->getHtml('News', 'News'); ?></div>
        <table class="default sticky">
            <thead>
                <td><?= $this->getHtml('Status', 'Tasks'); ?>
                <td><?= $this->getHtml('Due/Priority', 'Tasks'); ?>
                <td class="wf-100"><?= $this->getHtml('Title', 'Tasks'); ?>
            <tfoot>
            <tbody>
            <?php
            $c = 0;
            foreach ($this->tasks as $key => $task) : ++$c;
            $url = UriFactory::build(empty($task->redirect) ? 'task/view?{?}&id=' . $task->id : ($task->redirect));

            $color = 'darkred';
            if ($task->status === TaskStatus::DONE) { $color = 'green'; }
            elseif ($task->status === TaskStatus::OPEN) { $color = 'darkblue'; }
            elseif ($task->status === TaskStatus::WORKING) { $color = 'purple'; }
            elseif ($task->status === TaskStatus::CANCELED) { $color = 'red'; }
            elseif ($task->status === TaskStatus::SUSPENDED) { $color = 'yellow'; } ?>
                <tr data-href="<?= $url; ?>">
                    <td><a href="<?= $url; ?>"><span class="tag <?= $this->printHtml($color); ?>"><?= $this->getHtml('S' . $task->status, 'Tasks'); ?></span></a>
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
        <div class="portlet-foot"></div>
    </div>
</div>
