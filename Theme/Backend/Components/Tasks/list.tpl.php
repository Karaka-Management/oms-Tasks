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
 * @link      https://karaka.app
 */
declare(strict_types=1);

use Modules\Tasks\Models\TaskPriority;
use Modules\Tasks\Models\TaskStatus;
use phpOMS\Uri\UriFactory;

?>

<div id="news-dashboard" class="col-xs-12 col-md-6" draggable="true">
    <div class="portlet">
        <div class="portlet-head"><?= $this->getHtml('News', 'News'); ?></div>
        <table class="default">
            <thead>
                <td><?= $this->getHtml('Status', 'Tasks'); ?>
                <td><?= $this->getHtml('Due/Priority', 'Tasks'); ?>
                <td class="wf-100"><?= $this->getHtml('Title', 'Tasks'); ?>
            <tfoot>
            <tbody>
            <?php
            $c = 0;
            foreach ($this->tasks as $key => $task) : ++$c;
            $url = UriFactory::build(!empty($task->redirect) ? $task->redirect : ('task/single?{?}&id=' . $task->getId()));

            $color                                                         = 'darkred';
            if ($task->getStatus() === TaskStatus::DONE) { $color          = 'green'; }
            elseif ($task->getStatus() === TaskStatus::OPEN) { $color      = 'darkblue'; }
            elseif ($task->getStatus() === TaskStatus::WORKING) { $color   = 'purple'; }
            elseif ($task->getStatus() === TaskStatus::CANCELED) { $color  = 'red'; }
            elseif ($task->getStatus() === TaskStatus::SUSPENDED) { $color = 'yellow'; } ?>
                <tr data-href="<?= $url; ?>">
                    <td><a href="<?= $url; ?>"><span class="tag <?= $this->printHtml($color); ?>"><?= $this->getHtml('S' . $task->getStatus(), 'Tasks'); ?></span></a>
                    <td><a href="<?= $url; ?>">
                        <?php if ($task->getPriority() === TaskPriority::NONE) : ?>
                            <?= $this->printHtml($task->due->format('Y-m-d H:i')); ?>
                        <?php else : ?>
                            <?= $this->getHtml('P' . $task->getPriority()); ?>
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