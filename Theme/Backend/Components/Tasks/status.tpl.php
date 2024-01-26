<?php declare(strict_types=1);

use Modules\Tasks\Models\TaskStatus;

?>
<div class="ipt-wrap">
    <div class="ipt-first">
        <span class="input">
            <div class="advancedSelect" id="<?= $this->id; ?>"
                    data-search="true"
                    data-multiple="false"
                    data-src="api/admin/find/accgrp?search={!#i<?= $this->id; ?>}">
                <template><!-- Template for the selected element --></template>
            </div>
            <div id="<?= $this->id; ?>-popup" class="popup" data-active="true" data-selected="<?= $task->status; ?>">
                <template class="rowTemplate"><!-- Template for remote data or data manually to be added --></template>
                <tr><td data-value="<?= TaskStatus::OPEN; ?>"><?= $this->getHtml('S1'); ?>
                <tr><td data-value="<?= TaskStatus::WORKING; ?>"><?= $this->getHtml('S2'); ?>
                <tr><td data-value="<?= TaskStatus::SUSPENDED; ?>"><?= $this->getHtml('S3'); ?>
                <tr><td data-value="<?= TaskStatus::CANCELED; ?>"><?= $this->getHtml('S4'); ?>
                <tr><td data-value="<?= TaskStatus::DONE; ?>"><?= $this->getHtml('S5'); ?>
            </div>
        </span>
    </div>
</div>