<?php
/**
 * Jingga
 *
 * PHP Version 8.1
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 2.0
 * @version   1.0.0
 * @link      https://jingga.app
 */
declare(strict_types=1);

namespace Modules\Tasks\tests\Models;

use Modules\Admin\Models\NullAccount;
use Modules\Admin\Models\NullGroup;
use Modules\Tasks\Models\Task;
use Modules\Tasks\Models\TaskElement;
use Modules\Tasks\Models\TaskPriority;
use Modules\Tasks\Models\TaskStatus;
use Modules\Tasks\Models\TaskType;

/**
 * @internal
 */
final class TaskTest extends \PHPUnit\Framework\TestCase
{
    private Task $task;

    /**
     * {@inheritdoc}
     */
    protected function setUp() : void
    {
        $this->task = new Task();
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testDefault() : void
    {
        self::assertEquals(0, $this->task->id);
        self::assertEquals(0, $this->task->getCreatedBy()->id);
        self::assertEquals('', $this->task->title);
        self::assertFalse($this->task->isToAccount(0));
        self::assertFalse($this->task->isCCAccount(0));
        self::assertFalse($this->task->isToGroup(0));
        self::assertFalse($this->task->isCCGroup(0));
        self::assertTrue($this->task->isEditable);
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->task->createdAt->format('Y-m-d'));
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $this->task->start->format('Y-m-d'));
        self::assertNull($this->task->done);
        self::assertEquals((new \DateTime('now'))->modify('+1 day')->format('Y-m-d'), $this->task->due->format('Y-m-d'));
        self::assertEquals(TaskStatus::OPEN, $this->task->status);
        self::assertTrue($this->task->isClosable);
        self::assertEquals(TaskPriority::NONE, $this->task->priority);
        self::assertEquals(TaskType::SINGLE, $this->task->type);
        self::assertEquals([], $this->task->getTaskElements());
        self::assertEquals([], $this->task->files);
        self::assertEquals([], $this->task->tags);
        self::assertEquals('', $this->task->description);
        self::assertEquals('', $this->task->descriptionRaw);
        self::assertInstanceOf('\Modules\Tasks\Models\NullTaskElement', $this->task->getTaskElement(1));
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testCreatedByInputOutput() : void
    {
        $this->task->setCreatedBy(new NullAccount(1));
        self::assertEquals(1, $this->task->getCreatedBy()->id);
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testStartInputOutput() : void
    {
        $this->task->start = ($date = new \DateTime('2005-05-05'));
        self::assertEquals($date->format('Y-m-d'), $this->task->start->format('Y-m-d'));
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testTitleInputOutput() : void
    {
        $this->task->title = 'Title';
        self::assertEquals('Title', $this->task->title);
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testDoneInputOutput() : void
    {
        $this->task->done = ($date = new \DateTime('2000-05-06'));
        self::assertEquals($date->format('Y-m-d'), $this->task->done->format('Y-m-d'));
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testDueInputOutput() : void
    {
        $this->task->due = ($date = new \DateTime('2000-05-07'));
        self::assertEquals($date->format('Y-m-d'), $this->task->due->format('Y-m-d'));
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testClosableInputOutput() : void
    {
        $this->task->isClosable = false;
        self::assertFalse($this->task->isClosable);
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testElementInputOutput() : void
    {
        $taskElement1 = new TaskElement();
        $taskElement1->addTo(new NullAccount(2));
        $taskElement1->addGroupTo(new NullGroup(4));
        $taskElement1->addCC(new NullAccount(6));
        $taskElement1->addGroupCC(new NullGroup(8));

        $taskElement2 = new TaskElement();
        $taskElement2->addTo(new NullAccount(3));
        $taskElement2->addGroupTo(new NullGroup(5));
        $taskElement2->addCC(new NullAccount(7));
        $taskElement2->addGroupCC(new NullGroup(9));

        $id   = [];
        $id[] = $this->task->addElement($taskElement1);
        $id[] = $this->task->addElement($taskElement2);

        self::assertTrue($this->task->isToAccount(2));
        self::assertTrue($this->task->isToAccount(3));
        self::assertTrue($this->task->isToGroup(4));
        self::assertTrue($this->task->isToGroup(5));

        self::assertTrue($this->task->isCCAccount(6));
        self::assertTrue($this->task->isCCAccount(7));
        self::assertTrue($this->task->isCCGroup(8));
        self::assertTrue($this->task->isCCGroup(9));

        self::assertEquals(0, $this->task->getTaskElements()[0]->id);
        self::assertEquals(0, $this->task->getTaskElement(0)->id);
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testElementRemoval() : void
    {
        $taskElement1 = new TaskElement();
        $taskElement1->addTo(new NullAccount(2));
        $taskElement1->addGroupTo(new NullGroup(4));
        $taskElement1->addCC(new NullAccount(6));
        $taskElement1->addGroupCC(new NullGroup(8));

        $taskElement2 = new TaskElement();
        $taskElement2->addTo(new NullAccount(3));
        $taskElement2->addGroupTo(new NullGroup(5));
        $taskElement2->addCC(new NullAccount(7));
        $taskElement2->addGroupCC(new NullGroup(9));

        $id   = [];
        $id[] = $this->task->addElement($taskElement1);
        $id[] = $this->task->addElement($taskElement2);

        $success = $this->task->removeElement($id[1]);
        self::assertTrue($success);
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testInvertElements() : void
    {
        $taskElement1 = new TaskElement();
        $taskElement1->addTo(new NullAccount(2));
        $taskElement1->addGroupTo(new NullGroup(4));
        $taskElement1->addCC(new NullAccount(6));
        $taskElement1->addGroupCC(new NullGroup(8));

        $taskElement2 = new TaskElement();
        $taskElement2->addTo(new NullAccount(3));
        $taskElement2->addGroupTo(new NullGroup(5));
        $taskElement2->addCC(new NullAccount(7));
        $taskElement2->addGroupCC(new NullGroup(9));

        $this->task->addElement($taskElement1);
        $this->task->addElement($taskElement2);

        self::assertEquals([$taskElement2, $taskElement1], $this->task->invertTaskElements());
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testInvalidElementRemoval() : void
    {
        $success = $this->task->removeElement(99);
        self::assertFalse($success);
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testDescriptionInputOutput() : void
    {
        $this->task->description = 'Description';
        self::assertEquals('Description', $this->task->description);
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testDescriptionRawInputOutput() : void
    {
        $this->task->descriptionRaw = 'DescriptionRaw';
        self::assertEquals('DescriptionRaw', $this->task->descriptionRaw);
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testEditableInputOutput() : void
    {
        $this->task->isEditable = false;
        self::assertFalse($this->task->isEditable);
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testToArray() : void
    {
        $arr = [
            'id'          => 0,
            'createdBy'   => $this->task->getCreatedBy(),
            'createdAt'   => $this->task->createdAt,
            'title'       => $this->task->title,
            'description' => $this->task->description,
            'status'      => $this->task->status,
            'type'        => $this->task->type,
            'priority'    => $this->task->priority,
            'due'         => $this->task->due,
            'done'        => $this->task->done,
        ];

        $isSubset = true;
        $parent   = $this->task->toArray();
        foreach ($arr as $key => $value) {
            if (!\array_key_exists($key, $parent) || $parent[$key] !== $value) {
                $isSubset = false;
                break;
            }
        }
        self::assertTrue($isSubset);
    }

    /**
     * @covers Modules\Tasks\Models\Task
     * @group module
     */
    public function testToJson() : void
    {
        $arr = [
            'id'          => 0,
            'createdBy'   => $this->task->getCreatedBy(),
            'createdAt'   => $this->task->createdAt,
            'title'       => $this->task->title,
            'description' => $this->task->description,
            'status'      => $this->task->status,
            'type'        => $this->task->type,
            'priority'    => $this->task->priority,
            'due'         => $this->task->due,
            'done'        => $this->task->done,
        ];

        $isSubset = true;
        $parent   = $this->task->jsonSerialize();
        foreach ($arr as $key => $value) {
            if (!\array_key_exists($key, $parent) || $parent[$key] !== $value) {
                $isSubset = false;
                break;
            }
        }
        self::assertTrue($isSubset);
    }
}
