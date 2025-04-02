<?php
/**
 * Jingga
 *
 * PHP Version 8.2
 *
 * @package   Modules\Tasks
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.2
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

use Modules\Tasks\Models\TaskPriority;
use Modules\Tasks\Models\TaskType;

/** @var \phpOMS\Views\View $this */
echo $this->data['nav']->render(); ?>

<div class="row">
    <div class="col-xs-12 col-md-6">
        <section class="portlet">
            <form id="fTask" method="PUT" action="<?= \phpOMS\Uri\UriFactory::build('{/api}task?{?}&csrf={$CSRF}'); ?>">
                <div class="portlet-head"><?= $this->getHtml('Task'); ?></div>
                <div class="portlet-body">
                    <div class="form-group">
                        <label for="iiReceiver"><?= $this->getHtml('To'); ?></label>
                        <?= $this->getData('accGrpSelector')->render('iReceiver', 'forward', true); ?>
                    </div>

                    <div class="form-group">
                        <label for="iObserver"><?= $this->getHtml('CC'); ?></label>
                        <?= $this->getData('accGrpSelector')->render('iCC', 'cc', false); ?>
                    </div>

                    <div class="form-group">
                        <label for="iPriority"><?= $this->getHtml('Priority'); ?></label>
                        <select id="iPriority" name="priority">
                            <option value="<?= TaskPriority::NONE; ?>" selected><?= $this->getHtml('P0'); ?>
                            <option value="<?= TaskPriority::VLOW; ?>"><?= $this->getHtml('P1'); ?>
                            <option value="<?= TaskPriority::LOW; ?>"><?= $this->getHtml('P2'); ?>
                            <option value="<?= TaskPriority::MEDIUM; ?>"><?= $this->getHtml('P3'); ?>
                            <option value="<?= TaskPriority::HIGH; ?>"><?= $this->getHtml('P4'); ?>
                            <option value="<?= TaskPriority::VHIGH; ?>"><?= $this->getHtml('P5'); ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="iDue"><?= $this->getHtml('Due'); ?></label>
                        <input type="datetime-local" id="iDue" name="due" value="<?= $this->printHtml((new \DateTime('NOW'))->format('Y-m-d\TH:i:s')); ?>">
                    </div>

                    <div class="form-group">
                        <label for="iTitle"><?= $this->getHtml('Title'); ?></label>
                        <input type="text" id="iTitle" name="title" required>
                    </div>

                    <div class="form-group">
                        <?= $this->data['editor']->render('task-editor'); ?>
                    </div>

                    <div class="form-group">
                        <?= $this->data['editor']->getData('text')->render('task-editor', 'plain', 'fTask'); ?>
                    </div>
                </div>
                <div class="portlet-foot">
                    <input id="iCreateSubmit" type="submit" value="<?= $this->getHtml('Create', '0', '0'); ?>">
                    <input type="hidden" name="type" value="<?= TaskType::SINGLE; ?>">
                </div>
            </form>
        </section>
    </div>

    <div class="col-xs-12 col-md-6">
        <section class="portlet">
            <div class="portlet-head"><?= $this->getHtml('Media'); ?></div>
            <div class="portlet-body">
                <form>
                    <div class="form-group">
                        <label for="iMedia"><?= $this->getHtml('Media'); ?></label>
                        <div class="ipt-wrap wf-100">
                            <div class="ipt-first"><input type="text" id="iMedia" name="mediaFile"></div>
                            <div class="ipt-second"><button><?= $this->getHtml('Select'); ?></button></div>
                        </div>
                    </div>

                     <div class="form-group">
                        <input type="file" id="iUpload" name="upload" form="fTask" multiple>
                        <input form="fTask" type="hidden" name="type"><td>
                    </div>
                </div>
            </form>
        </section>
    </div>
</div>

<?= $this->getData('accGrpSelector')->getData('popup')->render(); ?>