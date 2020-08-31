<?php
/**
 * Orange Management
 *
 * PHP Version 7.4
 *
 * @package   tests
 * @copyright Dennis Eichhorn
 * @license   OMS License 1.0
 * @version   1.0.0
 * @link      https://orange-management.org
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
class TaskTest extends \PHPUnit\Framework\TestCase
{
    public function testDefault() : void
    {
        $task = new Task();

        self::assertEquals(0, $task->getId());
        self::assertEquals(0, $task->getCreatedBy()->getId());
        self::assertEquals('', $task->getTitle());
        self::assertFalse($task->isToAccount(0));
        self::assertFalse($task->isCCAccount(0));
        self::assertFalse($task->isToGroup(0));
        self::assertFalse($task->isCCGroup(0));
        self::assertTrue($task->isEditable());
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $task->getCreatedAt()->format('Y-m-d'));
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $task->getStart()->format('Y-m-d'));
        self::assertNull($task->getDone());
        self::assertEquals((new \DateTime('now'))->modify('+1 day')->format('Y-m-d'), $task->getDue()->format('Y-m-d'));
        self::assertEquals(TaskStatus::OPEN, $task->getStatus());
        self::assertTrue($task->isClosable());
        self::assertEquals(TaskPriority::NONE, $task->getPriority());
        self::assertEquals(TaskType::SINGLE, $task->getType());
        self::assertEquals([], $task->getTaskElements());
        self::assertEquals('', $task->getDescription());
        self::assertEquals('', $task->getDescriptionRaw());
        self::assertInstanceOf('\Modules\Tasks\Models\NullTaskElement', $task->getTaskElement(1));
    }

    public function testCreatedByInputOutput() : void
    {
        $task = new Task();

        $task->setCreatedBy(new NullAccount(1));
        self::assertEquals(1, $task->getCreatedBy()->getId());
    }

    public function testStartInputOutput() : void
    {
        $task = new Task();

        $task->setStart($date = new \DateTime('2005-05-05'));
        self::assertEquals($date->format('Y-m-d'), $task->getStart()->format('Y-m-d'));
    }

    public function testTitleInputOutput() : void
    {
        $task = new Task();

        $task->setTitle('Title');
        self::assertEquals('Title', $task->getTitle());
    }

    public function testDoneInputOutput() : void
    {
        $task = new Task();

        $task->setDone($date = new \DateTime('2000-05-06'));
        self::assertEquals($date->format('Y-m-d'), $task->getDone()->format('Y-m-d'));
    }

    public function testDueInputOutput() : void
    {
        $task = new Task();

        $task->setDue($date = new \DateTime('2000-05-07'));
        self::assertEquals($date->format('Y-m-d'), $task->getDue()->format('Y-m-d'));
    }

    public function testStatusInputOutput() : void
    {
        $task = new Task();

        $task->setStatus(TaskStatus::DONE);
        self::assertEquals(TaskStatus::DONE, $task->getStatus());
    }

    public function testClosableInputOutput() : void
    {
        $task = new Task();

        $task->setClosable(false);
        self::assertFalse($task->isClosable());
    }

    public function testPriority() : void
    {
        $task = new Task();

        $task->setPriority(TaskPriority::LOW);
        self::assertEquals(TaskPriority::LOW, $task->getPriority());
    }

    public function testElementInputOutput() : void
    {
        $task = new Task();

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
        $id[] = $task->addElement($taskElement1);
        $id[] = $task->addElement($taskElement2);

        self::assertTrue($task->isToAccount(2));
        self::assertTrue($task->isToAccount(3));
        self::assertTrue($task->isToGroup(4));
        self::assertTrue($task->isToGroup(5));

        self::assertTrue($task->isCCAccount(6));
        self::assertTrue($task->isCCAccount(7));
        self::assertTrue($task->isCCGroup(8));
        self::assertTrue($task->isCCGroup(9));

        self::assertEquals(0, $task->getTaskElements()[0]->getId());
        self::assertEquals(0, $task->getTaskElement(0)->getId());
    }

    public function testElementRemoval() : void
    {
        $task = new Task();

        $task = new Task();

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
        $id[] = $task->addElement($taskElement1);
        $id[] = $task->addElement($taskElement2);

        $success = $task->removeElement($id[1]);
        self::assertTrue($success);
    }

    public function testInvalidElementRemoval() : void
    {
        $task = new Task();

        $success = $task->removeElement(99);
        self::assertFalse($success);
    }

    public function testDescriptionInputOutput() : void
    {
        $task = new Task();

        $task->setDescription('Description');
        self::assertEquals('Description', $task->getDescription());
    }

    public function testDescriptionRawInputOutput() : void
    {
        $task = new Task();

        $task->setDescriptionRaw('DescriptionRaw');
        self::assertEquals('DescriptionRaw', $task->getDescriptionRaw());
    }

    public function testEditableInputOutput() : void
    {
        $task = new Task();

        $task->setEditable(false);
        self::assertFalse($task->isEditable());
    }

    public function testToArray() : void
    {
        $task = new Task();

        $arr = [
            'id'          => 0,
            'createdBy'   => $task->getCreatedBy(),
            'createdAt'   => $task->getCreatedAt(),
            'title'       => $task->getTitle(),
            'description' => $task->getDescription(),
            'status'      => $task->getStatus(),
            'type'        => $task->getType(),
            'priority'    => $task->getPriority(),
            'due'         => $task->getDue(),
            'done'        => $task->getDone(),
        ];

        $isSubset = true;
        $parent   = $task->toArray();
        foreach ($arr as $key => $value) {
            if (!\array_key_exists($key, $parent) || $parent[$key] !== $value) {
                $isSubset = false;
                break;
            }
        }
        self::assertTrue($isSubset);
    }

    public function testToJson() : void
    {
        $task = new Task();

        $arr = [
            'id'          => 0,
            'createdBy'   => $task->getCreatedBy(),
            'createdAt'   => $task->getCreatedAt(),
            'title'       => $task->getTitle(),
            'description' => $task->getDescription(),
            'status'      => $task->getStatus(),
            'type'        => $task->getType(),
            'priority'    => $task->getPriority(),
            'due'         => $task->getDue(),
            'done'        => $task->getDone(),
        ];

        $isSubset = true;
        $parent   = $task->jsonSerialize();
        foreach ($arr as $key => $value) {
            if (!\array_key_exists($key, $parent) || $parent[$key] !== $value) {
                $isSubset = false;
                break;
            }
        }
        self::assertTrue($isSubset);
    }

    public function testInvalidStatus() : void
    {
        $this->expectException(\phpOMS\Stdlib\Base\Exception\InvalidEnumValue::class);

        $task = new Task();
        $task->setStatus(9999);
    }

    public function testInvalidPriority() : void
    {
        $this->expectException(\phpOMS\Stdlib\Base\Exception\InvalidEnumValue::class);

        $task = new Task();
        $task->setPriority(9999);
    }
}
