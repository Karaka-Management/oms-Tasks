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

use \Modules\Tasks\Models\TaskPriority;
use \Modules\Tasks\Models\TaskType;

/** @var \phpOMS\Views\View $this */
echo $this->getData('nav')->render(); ?>

<div class="row">
    <div class="col-xs-12 col-md-6">
        <div class="portlet">
            <form id="fTask" method="PUT" action="<?= \phpOMS\Uri\UriFactory::build('{/api}task?{?}&csrf={$CSRF}'); ?>">
                <div class="portlet-head"><?= $this->getHtml('Task'); ?></div>
                <div class="portlet-body">
                    <table class="layout wf-100" style="table-layout: fixed">
                        <tbody>
                        <tr><td><label for="iReceiver"><?= $this->getHtml('To'); ?></label>
                        <tr><td><?= $this->getData('accGrpSelector')->render('iReceiver', 'forward', true); ?>
                        <tr><td><label for="iObserver"><?= $this->getHtml('CC'); ?></label>
                        <tr><td><?= $this->getData('accGrpSelector')->render('iCC', 'cc', false); ?>
                        <tr><td><label for="iPriority"><?= $this->getHtml('Priority'); ?></label>
                        <tr><td>
                            <select id="iPriority" name="priority">
                                <option value="<?= TaskPriority::NONE; ?>" selected><?= $this->getHtml('P0'); ?>
                                <option value="<?= TaskPriority::VLOW; ?>"><?= $this->getHtml('P1'); ?>
                                <option value="<?= TaskPriority::LOW; ?>"><?= $this->getHtml('P2'); ?>
                                <option value="<?= TaskPriority::MEDIUM; ?>"><?= $this->getHtml('P3'); ?>
                                <option value="<?= TaskPriority::HIGH; ?>"><?= $this->getHtml('P4'); ?>
                                <option value="<?= TaskPriority::VHIGH; ?>"><?= $this->getHtml('P5'); ?>
                            </select>
                        <tr><td><label for="iDue"><?= $this->getHtml('Due'); ?></label>
                        <tr><td><input type="datetime-local" id="iDue" name="due" value="<?= $this->printHtml((new \DateTime('NOW'))->format('Y-m-d\TH:i:s')); ?>">
                        <tr><td><label for="iTitle"><?= $this->getHtml('Title'); ?></label>
                        <tr><td><input type="text" id="iTitle" name="title" placeholder="&#xf040; <?= $this->getHtml('Title'); ?>" required>
                        <tr><td><label for="iMessage"><?= $this->getHtml('Message'); ?></label>
                        <tr><td><?= $this->getData('editor')->render('task-editor'); ?>
                        <tr><td><?= $this->getData('editor')->getData('text')->render('task-editor', 'plain', 'fTask'); ?>
                    </table>
                </div>
                <div class="portlet-foot">
                    <input id="iCreateSubmit" type="submit" value="<?= $this->getHtml('Create', '0', '0'); ?>">
                    <input type="hidden" name="type" value="<?= $this->printHtml(TaskType::SINGLE); ?>">
                </div>
            </form>
        </div>
    </div>

    <div class="col-xs-12 col-md-6">
        <div class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Media'); ?></div>
            <div class="portlet-body">
                <form>
                    <table class="layout wf-100">
                        <tbody>
                        <tr><td><label for="iMedia"><?= $this->getHtml('Media'); ?></label>
                        <tr><td>
                            <div class="ipt-wrap">
                                <div class="ipt-first"><input type="text" id="iMedia" name="mediaFile" placeholder="&#xf15b; File"></div>
                                <div class="ipt-second"><button><?= $this->getHtml('Select'); ?></button></div>
                            </div>
                        <tr><td><label for="iUpload"><?= $this->getHtml('Upload'); ?></label>
                        <tr><td>
                            <input type="file" id="iUpload" name="upload" form="fTask" multiple>
                            <input form="fTask" type="hidden" name="type"><td>
                    </table>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->getData('accGrpSelector')->getData('popup')->render(); ?>