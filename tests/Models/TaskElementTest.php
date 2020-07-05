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
use Modules\Tasks\Models\TaskElement;
use Modules\Tasks\Models\TaskPriority;
use Modules\Tasks\Models\TaskStatus;

/**
 * @internal
 */
class TaskElementTest extends \PHPUnit\Framework\TestCase
{
    public function testDefault() : void
    {
        $task = new TaskElement();

        self::assertEquals(0, $task->getId());
        self::assertEquals(0, $task->getCreatedBy()->getId());
        self::assertEquals((new \DateTime('now'))->format('Y-m-d'), $task->getCreatedAt()->format('Y-m-d'));
        self::assertEquals((new \DateTime('now'))->modify('+1 day')->format('Y-m-d'), $task->getDue()->format('Y-m-d'));
        self::assertEquals(TaskStatus::OPEN, $task->getStatus());
        self::assertEquals('', $task->getDescription());
        self::assertEquals('', $task->getDescriptionRaw());
        self::assertEquals([], $task->getTo());
        self::assertEquals([], $task->getCC());
        self::assertEquals(0, $task->getTask());
        self::assertEquals(TaskPriority::NONE, $task->getPriority());
    }

    public function testCreatedByInputOutput() : void
    {
        $task = new TaskElement();

        $task->setCreatedBy(new NullAccount(1));
        self::assertEquals(1, $task->getCreatedBy()->getId());
    }

    public function testDueInputOutput() : void
    {
        $task = new TaskElement();

        $task->setDue($date = new \DateTime('2000-05-07'));
        self::assertEquals($date->format('Y-m-d'), $task->getDue()->format('Y-m-d'));
    }

    public function testStatusInputOutput() : void
    {
        $task = new TaskElement();

        $task->setStatus(TaskStatus::DONE);
        self::assertEquals(TaskStatus::DONE, $task->getStatus());
    }

    public function testPriorityInputOutput() : void
    {
        $task = new TaskElement();

        $task->setPriority(TaskPriority::MEDIUM);
        self::assertEquals(TaskPriority::MEDIUM, $task->getPriority());
    }

    public function testDescriptionInputOutput() : void
    {
        $task = new TaskElement();

        $task->setDescription('Description');
        self::assertEquals('Description', $task->getDescription());
    }

    public function testDescriptionRawInputOutput() : void
    {
        $task = new TaskElement();

        $task->setDescriptionRaw('DescriptionRaw');
        self::assertEquals('DescriptionRaw', $task->getDescriptionRaw());
    }

    public function testTaskInputOutput() : void
    {
        $task = new TaskElement();

        $task->setTask(2);
        self::assertEquals(2, $task->getTask());
    }

    public function testAccountToInputOutput() : void
    {
        $task = new TaskElement();

        $task->addTo(new NullAccount(3));
        $task->addTo(new NullAccount(3)); // test duplicate
        self::assertTrue($task->isToAccount(3));
    }

    public function testGroupToInputOutput() : void
    {
        $task = new TaskElement();

        $task->addGroupTo(new NullGroup(4));
        $task->addGroupTo(new NullGroup(4)); // test duplicate
        self::assertTrue($task->isToGroup(4));
    }

    public function testAccountCCInputOutput() : void
    {
        $task = new TaskElement();

        $task->addCC(new NullAccount(5));
        $task->addCC(new NullAccount(5)); // test duplicate
        self::assertTrue($task->isCCAccount(5));
    }

    public function testGroupCCInputOutput() : void
    {
        $task = new TaskElement();

        $task->addGroupCC(new NullGroup(6));
        $task->addGroupCC(new NullGroup(6)); // test duplicate
        self::assertTrue($task->isCCGroup(6));
    }

    public function testInvalidAccountTo() : void
    {
        $task = new TaskElement();

        self::assertFalse($task->isToAccount(7));
    }

    public function testInvalidAccountCC() : void
    {
        $task = new TaskElement();

        self::assertFalse($task->isCCAccount(8));
    }

    public function testInvalidGroupTo() : void
    {
        $task = new TaskElement();

        self::assertFalse($task->isToGroup(9));
    }

    public function testInvalidGroupCC() : void
    {
        $task = new TaskElement();

        self::assertFalse($task->isCCGroup(10));
    }

    public function testInvalidStatus() : void
    {
        $this->expectException(\phpOMS\Stdlib\Base\Exception\InvalidEnumValue::class);

        $task = new TaskElement();
        $task->setStatus(9999);
    }

    public function testInvalidPriority() : void
    {
        $this->expectException(\phpOMS\Stdlib\Base\Exception\InvalidEnumValue::class);

        $task = new TaskElement();
        $task->setPriority(9999);
    }
}
