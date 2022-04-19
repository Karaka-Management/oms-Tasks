<?php
/**
 * Karaka
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://karaka.app
 */
declare(strict_types=1);

namespace Modules\Tasks\tests\Models;

use Modules\Admin\Models\NullAccount;
use Modules\Admin\Models\NullGroup;
use Modules\Media\Models\Media;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskElement;
use Modules\Tasks\Models\TaskMapper;
use Modules\Tasks\Models\TaskPriority;
use Modules\Tasks\Models\TaskStatus;
use phpOMS\DataStorage\Database\Query\OrderType;
use phpOMS\Utils\RnG\Text;

/**
 * @internal
 */
final class TaskMapperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @covers Modules\Tasks\Models\TaskMapper
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals([], TaskMapper::getOpenCreatedBy(999));
        self::assertEquals(0, TaskMapper::countUnread(999));
    }

    /**
     * @covers Modules\Tasks\Models\TaskMapper
     * @group module
     */
    public function testCRUD() : void
    {
        $task = new Task();

        $task->setCreatedBy(new NullAccount(1));
        $task->schedule->createdBy      = new NullAccount(1);
        $task->start                    = new \DateTime('2005-05-05');
        $task->title                    = 'Task Test';
        $task->setStatus(TaskStatus::OPEN);
        $task->isClosable = false;
        $task->setPriority(TaskPriority::HIGH);
        $task->description    = 'Description';
        $task->descriptionRaw = 'DescriptionRaw';
        $task->done           = new \DateTime('2000-05-06');
        $task->due            = new \DateTime('2000-05-05');

        $taskElement1              = new TaskElement();
        $taskElement1->description = 'Desc1';
        $taskElement1->createdBy   = new NullAccount(1);
        $taskElement1->setStatus($task->getStatus());
        $task->addElement($taskElement1);

        $media              = new Media();
        $media->createdBy   = new NullAccount(1);
        $media->description = 'desc';
        $media->setPath('some/path');
        $media->size      = 11;
        $media->extension = 'png';
        $media->name      = 'Task Element Media';
        $taskElement1->addMedia($media);

        $taskElement2              = new TaskElement();
        $taskElement2->description = 'Desc2';
        $taskElement2->createdBy   = new NullAccount(1);
        $taskElement2->setStatus($task->getStatus());
        $taskElement2->addAccountTo(new NullAccount(1));
        $taskElement2->addAccountCC(new NullAccount(1));
        $taskElement2->addGroupTo(new NullGroup(1));
        $taskElement2->addGroupCC(new NullGroup(1));
        $task->addElement($taskElement2);

        $media              = new Media();
        $media->createdBy   = new NullAccount(1);
        $media->description = 'desc';
        $media->setPath('some/path');
        $media->size      = 11;
        $media->extension = 'png';
        $media->name      = 'Task Media';
        $task->addMedia($media);

        $id = TaskMapper::create()->execute($task);
        self::assertGreaterThan(0, $task->getId());
        self::assertEquals($id, $task->getId());

        $taskR = TaskMapper::get()
            ->with('media')
            ->with('taskElements')
            ->with('taskElements/media')
            ->with('taskElements/accRelation')
            ->with('taskElements/accRelation/relation')
            ->with('taskElements/grpRelation')
            ->with('taskElements/grpRelation/relation')
            ->where('id', $task->getId())
            ->execute();
        self::assertEquals($task->createdAt->format('Y-m-d'), $taskR->createdAt->format('Y-m-d'));
        self::assertEquals($task->start->format('Y-m-d'), $taskR->start->format('Y-m-d'));
        self::assertEquals($task->getCreatedBy()->getId(), $taskR->getCreatedBy()->getId());
        self::assertEquals($task->description, $taskR->description);
        self::assertEquals($task->descriptionRaw, $taskR->descriptionRaw);
        self::assertEquals($task->title, $taskR->title);
        self::assertEquals($task->getStatus(), $taskR->getStatus());
        self::assertEquals($task->isClosable, $taskR->isClosable);
        self::assertEquals($task->getType(), $taskR->getType());
        self::assertEquals($task->done->format('Y-m-d'), $taskR->done->format('Y-m-d'));
        self::assertEquals($task->due->format('Y-m-d'), $taskR->due->format('Y-m-d'));
        self::assertGreaterThan(0, TaskMapper::countUnread(1));

        $expected = $task->getMedia();
        $actual   = $taskR->getMedia();
        self::assertEquals(\end($expected)->name, \end($actual)->name);

        $expected = $task->getTaskElements();
        $actual   = $taskR->getTaskElements();

        $expectedMedia = \reset($expected)->getMedia();
        $actualMedia   = \reset($actual)->getMedia();

        self::assertEquals(\end($expected)->description, \end($actual)->description);
        self::assertEquals(\end($expectedMedia)->name, \end($actualMedia)->name);

        self::assertTrue(\end($actual)->isToAccount(1));
        self::assertTrue(\end($actual)->isToGroup(1));
        self::assertTrue(\end($actual)->isCCAccount(1));
        self::assertTrue(\end($actual)->isCCGroup(1));

        self::assertCount(2, \end($actual)->getTo());
        self::assertCount(2, \end($actual)->getCC());
    }

    /**
     * @covers Modules\Tasks\Models\TaskMapper
     * @group module
     */
    public function testNewest() : void
    {
        $newest = TaskMapper::getAll()->sort('id', OrderType::DESC)->limit(1)->execute();

        self::assertCount(1, $newest);
    }

    /**
     * @group volume
     * @group module
     * @coversNothing
     */
    public function testCreatedByMeForMe() : void
    {
        $text = new Text();

        $taskStatus = TaskStatus::getConstants();

        foreach ($taskStatus as $status) {
            $task = new Task();

            $task->setCreatedBy(new NullAccount(1));
            $task->schedule->createdBy      = new NullAccount(1);
            $task->start                    = new \DateTime('2005-05-05');
            $task->title                    = $text->generateText(\mt_rand(1, 5));
            $task->setStatus($status);
            $task->description = $text->generateText(\mt_rand(10, 30));
            $task->done        = new \DateTime('2000-05-06');
            $task->due         = new \DateTime('2000-05-05');

            $taskElement1              = new TaskElement();
            $taskElement1->description = $text->generateText(\mt_rand(3, 20));
            $taskElement1->createdBy   = new NullAccount(1);
            $taskElement1->setStatus($status);
            $task->addElement($taskElement1);

            $taskElement2              = new TaskElement();
            $taskElement2->description = 'Desc2';
            $taskElement2->createdBy   = new NullAccount(1);
            $taskElement2->setStatus($status);
            $task->addElement($taskElement2);

            $id = TaskMapper::create()->execute($task);
        }

        self::assertGreaterThan(0, TaskMapper::countUnread(1));
    }

    /**
     * @group volume
     * @group module
     * @coversNothing
     */
    public function testCreatedByMeForOther() : void
    {
        $text = new Text();

        $taskStatus = TaskStatus::getConstants();

        foreach ($taskStatus as $status) {
            $task = new Task();

            $task->setCreatedBy(new NullAccount(1));
            $task->schedule->createdBy      = new NullAccount(1);
            $task->title                    = $text->generateText(\mt_rand(1, 5));
            $task->setStatus($status);
            $task->isClosable  = true;
            $task->description = $text->generateText(\mt_rand(10, 30));
            $task->done        = new \DateTime('2000-05-06');
            $task->due         = new \DateTime('2000-05-05');

            $taskElement1              = new TaskElement();
            $taskElement1->description = $text->generateText(\mt_rand(3, 20));
            $taskElement1->createdBy   = new NullAccount(1);
            $taskElement1->setStatus($status);
            $task->addElement($taskElement1);

            $taskElement2              = new TaskElement();
            $taskElement2->description = $text->generateText(\mt_rand(3, 20));
            $taskElement2->createdBy   = new NullAccount(1);
            $taskElement2->setStatus($status);
            $task->addElement($taskElement2);

            $id = TaskMapper::create()->execute($task);
        }
    }

    /**
     * @group volume
     * @group module
     * @coversNothing
     */
    public function testCreatedByOtherForMe() : void
    {
        $text = new Text();

        $taskStatus = TaskStatus::getConstants();

        foreach ($taskStatus as $status) {
            $task = new Task();

            $task->setCreatedBy(new NullAccount(1));
            $task->schedule->createdBy      = new NullAccount(1);
            $task->title                    = $text->generateText(\mt_rand(1, 5));
            $task->setStatus($status);
            $task->isClosable  = true;
            $task->description = $text->generateText(\mt_rand(10, 30));
            $task->done        = new \DateTime('2000-05-06');
            $task->due         = new \DateTime('2000-05-05');

            $taskElement1              = new TaskElement();
            $taskElement1->description = $text->generateText(\mt_rand(3, 20));
            $taskElement1->createdBy   = new NullAccount(1);
            $taskElement1->setStatus($status);
            $task->addElement($taskElement1);

            $taskElement2              = new TaskElement();
            $taskElement2->description = $text->generateText(\mt_rand(3, 20));
            $taskElement2->createdBy   = new NullAccount(1);
            $taskElement2->setStatus($status);
            $task->addElement($taskElement2);

            $id = TaskMapper::create()->execute($task);
        }
    }
}
